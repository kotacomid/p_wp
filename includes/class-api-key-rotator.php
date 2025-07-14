<?php
/**
 * API Key Rotator class
 * Handles multiple API keys per provider and automatic rotation
 */

if (!defined('ABSPATH')) {
    exit;
}

class KotacomAI_API_Key_Rotator {
    
    private $debug_logging = true; // Set to false to disable verbose logging
    
    private $rate_limit_errors = array(
        'quota exceeded',
        'rate limit',
        'too many requests',
        'limit exceeded',
        'quota_exceeded',
        'rate_limit_exceeded',
        'insufficient_quota',
        'rate limited',
        'billing_hard_limit_reached',
        'requests per minute',
        'requests per day',
        'monthly quota exceeded',
        'billing quota exceeded',
        'api rate limit exceeded',
        '429',  // HTTP status code for Too Many Requests
        'resource_exhausted',  // Google API
        'usage_limit_exceeded',
        'daily_limit_exceeded',
        'minute_quota_exceeded',
        'hour_quota_exceeded',
        'request_limit_exceeded',
        'tokens_per_minute_exceeded',
        'concurrent_requests_exceeded',
        'bandwidth_limit_exceeded'
    );
    
    /**
     * Debug logging helper
     */
    private function debug_log($message) {
        if ($this->debug_logging) {
            error_log($message);
        }
    }
    
    /**
     * Get active API key for provider
     */
    public function get_active_api_key($provider) {
        $keys = $this->get_provider_keys($provider);
        
        if (empty($keys)) {
            return false;
        }
        
        // Get current active key index
        $active_index = get_option("kotacom_ai_{$provider}_active_key_index", 0);
        
        // Validate index exists
        if (!isset($keys[$active_index])) {
            $active_index = 0;
            update_option("kotacom_ai_{$provider}_active_key_index", 0);
        }
        
        return isset($keys[$active_index]) ? $keys[$active_index] : false;
    }
    
    /**
     * Get all API keys for a provider
     */
    public function get_provider_keys($provider) {
        // Get multiple keys (new format)
        $multiple_keys = get_option("kotacom_ai_{$provider}_api_keys", array());
        
        // Debug logging
        $this->debug_log("KotacomAI API Rotator: Getting keys for provider: $provider");
        $this->debug_log("KotacomAI API Rotator: Multiple keys option (kotacom_ai_{$provider}_api_keys): " . print_r($multiple_keys, true));
        
        // Fallback to single key (legacy format)
        if (empty($multiple_keys)) {
            $single_key = get_option("kotacom_ai_{$provider}_api_key", '');
            error_log("KotacomAI API Rotator: No multiple keys found, checking single key option (kotacom_ai_{$provider}_api_key): " . (!empty($single_key) ? 'Found' : 'Empty'));
            
            if (!empty($single_key)) {
                return array($single_key);
            }
        }
        
        // Filter out empty keys
        $filtered_keys = array_filter($multiple_keys, function($key) {
            return !empty(trim($key));
        });
        
        error_log("KotacomAI API Rotator: Filtered keys count: " . count($filtered_keys));
        
        return $filtered_keys;
    }
    
    /**
     * Add API key for provider
     */
    public function add_api_key($provider, $api_key) {
        $api_key = trim($api_key);
        if (empty($api_key)) {
            return false;
        }
        
        $keys = $this->get_provider_keys($provider);
        
        // Check if key already exists
        if (in_array($api_key, $keys)) {
            return true;
        }
        
        $keys[] = $api_key;
        update_option("kotacom_ai_{$provider}_api_keys", $keys);
        
        return true;
    }
    
    /**
     * Remove API key for provider
     */
    public function remove_api_key($provider, $index) {
        $keys = $this->get_provider_keys($provider);
        
        if (!isset($keys[$index])) {
            return false;
        }
        
        unset($keys[$index]);
        $keys = array_values($keys); // Re-index array
        
        update_option("kotacom_ai_{$provider}_api_keys", $keys);
        
        // Reset active index if it's out of bounds
        $active_index = get_option("kotacom_ai_{$provider}_active_key_index", 0);
        if ($active_index >= count($keys)) {
            update_option("kotacom_ai_{$provider}_active_key_index", 0);
        }
        
        return true;
    }
    
    /**
     * Rotate to next API key for provider
     */
    public function rotate_api_key($provider, $reason = '') {
        $keys = $this->get_provider_keys($provider);
        
        error_log("KotacomAI API Rotator: Attempting rotation for provider: $provider. Available keys: " . count($keys));
        
        if (count($keys) <= 1) {
            error_log("KotacomAI API Rotator: Cannot rotate - only " . count($keys) . " key(s) available");
            return false; // No keys to rotate to
        }
        
        $current_index = get_option("kotacom_ai_{$provider}_active_key_index", 0);
        $next_index = ($current_index + 1) % count($keys);
        
        // Check if next key is in cooldown
        if ($this->is_key_in_cooldown($provider, $next_index)) {
            error_log("KotacomAI API Rotator: Next key (index $next_index) is in cooldown, finding available key");
            
            // Find a key that's not in cooldown
            for ($i = 0; $i < count($keys); $i++) {
                $test_index = ($current_index + $i + 1) % count($keys);
                if (!$this->is_key_in_cooldown($provider, $test_index)) {
                    $next_index = $test_index;
                    break;
                }
            }
        }
        
        update_option("kotacom_ai_{$provider}_active_key_index", $next_index);
        
        error_log("KotacomAI API Rotator: Rotated from index $current_index to index $next_index");
        
        // Log the rotation
        $this->log_key_rotation($provider, $current_index, $next_index, $reason);
        
        // Set cooldown for the rotated key
        $this->set_key_cooldown($provider, $current_index);
        
        return true;
    }
    
    /**
     * Check if error indicates rate limiting
     */
    public function is_rate_limit_error($error_message) {
        $error_lower = strtolower($error_message);
        
        foreach ($this->rate_limit_errors as $rate_error) {
            if (strpos($error_lower, $rate_error) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Handle API error and rotate if needed
     */
    public function handle_api_error($provider, $error_message) {
        error_log("KotacomAI API Rotator: Checking error for rate limit - " . $error_message);
        
        if ($this->is_rate_limit_error($error_message)) {
            error_log("KotacomAI API Rotator: Rate limit detected, attempting rotation");
            $rotated = $this->rotate_api_key($provider, 'Rate limit: ' . $error_message);
            
            if ($rotated) {
                error_log("KotacomAI API Rotator: Successfully rotated API key for provider: $provider");
                return array(
                    'rotated' => true,
                    'message' => 'API key rotated due to rate limit'
                );
            } else {
                error_log("KotacomAI API Rotator: Failed to rotate - no alternative keys available");
                return array(
                    'rotated' => false,
                    'message' => 'No alternative API keys available for rotation'
                );
            }
        }
        
        error_log("KotacomAI API Rotator: Error does not indicate rate limiting");
        return array(
            'rotated' => false,
            'message' => 'Error does not indicate rate limiting: ' . $error_message
        );
    }
    
    /**
     * Set cooldown for API key
     */
    private function set_key_cooldown($provider, $key_index, $duration = 3600) {
        $cooldown_key = "kotacom_ai_{$provider}_key_{$key_index}_cooldown";
        set_transient($cooldown_key, time(), $duration);
    }
    
    /**
     * Check if API key is in cooldown
     */
    public function is_key_in_cooldown($provider, $key_index) {
        $cooldown_key = "kotacom_ai_{$provider}_key_{$key_index}_cooldown";
        return get_transient($cooldown_key) !== false;
    }
    
    /**
     * Get next available API key (skip cooldowns)
     */
    public function get_next_available_key($provider) {
        $keys = $this->get_provider_keys($provider);
        $total_keys = count($keys);
        
        if ($total_keys === 0) {
            return false;
        }
        
        $current_index = get_option("kotacom_ai_{$provider}_active_key_index", 0);
        
        // Try each key starting from current
        for ($i = 0; $i < $total_keys; $i++) {
            $check_index = ($current_index + $i) % $total_keys;
            
            if (!$this->is_key_in_cooldown($provider, $check_index)) {
                // Update active index if we found a different key
                if ($check_index !== $current_index) {
                    update_option("kotacom_ai_{$provider}_active_key_index", $check_index);
                }
                return $keys[$check_index];
            }
        }
        
        // All keys are in cooldown, return current anyway
        return $keys[$current_index];
    }
    
    /**
     * Log key rotation
     */
    private function log_key_rotation($provider, $old_index, $new_index, $reason) {
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'provider' => $provider,
            'old_index' => $old_index,
            'new_index' => $new_index,
            'reason' => $reason
        );
        
        $log = get_option('kotacom_ai_key_rotation_log', array());
        array_unshift($log, $log_entry);
        
        // Keep only last 100 entries
        $log = array_slice($log, 0, 100);
        
        update_option('kotacom_ai_key_rotation_log', $log);
        
        // Also log via WordPress if debug is enabled
        if (defined('KOTACOM_AI_DEBUG') && KOTACOM_AI_DEBUG) {
            error_log("Kotacom AI: Rotated {$provider} API key from index {$old_index} to {$new_index}. Reason: {$reason}");
        }
    }
    
    /**
     * Get rotation statistics
     */
    public function get_rotation_stats($provider = null) {
        $log = get_option('kotacom_ai_key_rotation_log', array());
        
        if ($provider) {
            $log = array_filter($log, function($entry) use ($provider) {
                return $entry['provider'] === $provider;
            });
        }
        
        $stats = array(
            'total_rotations' => count($log),
            'last_24h' => 0,
            'last_7d' => 0,
            'by_provider' => array()
        );
        
        $now = time();
        $day_ago = $now - (24 * 3600);
        $week_ago = $now - (7 * 24 * 3600);
        
        foreach ($log as $entry) {
            $entry_time = strtotime($entry['timestamp']);
            
            if ($entry_time > $day_ago) {
                $stats['last_24h']++;
            }
            
            if ($entry_time > $week_ago) {
                $stats['last_7d']++;
            }
            
            $provider_name = $entry['provider'];
            if (!isset($stats['by_provider'][$provider_name])) {
                $stats['by_provider'][$provider_name] = 0;
            }
            $stats['by_provider'][$provider_name]++;
        }
        
        return $stats;
    }
    
    /**
     * Test all API keys for a provider
     */
    public function test_all_keys($provider) {
        $keys = $this->get_provider_keys($provider);
        $results = array();
        
        // Temporarily store current active key
        $original_active = get_option("kotacom_ai_{$provider}_active_key_index", 0);
        
        foreach ($keys as $index => $key) {
            // Set this key as active temporarily
            update_option("kotacom_ai_{$provider}_active_key_index", $index);
            
            // Test the key
            $api_handler = new KotacomAI_API_Handler();
            $test_result = $api_handler->test_api_connection($provider, $key);
            
            $results[$index] = array(
                'key' => substr($key, 0, 8) . '...' . substr($key, -4), // Masked key
                'success' => $test_result['success'],
                'message' => $test_result['message'] ?? '',
                'in_cooldown' => $this->is_key_in_cooldown($provider, $index)
            );
        }
        
        // Restore original active key
        update_option("kotacom_ai_{$provider}_active_key_index", $original_active);
        
        return $results;
    }
    
    /**
     * Import API keys from legacy single key format
     */
    public function migrate_legacy_keys() {
        $providers = array('google_ai', 'openai', 'groq', 'anthropic', 'cohere', 'huggingface', 'together', 'replicate', 'openrouter', 'perplexity');
        
        foreach ($providers as $provider) {
            $single_key = get_option("kotacom_ai_{$provider}_api_key", '');
            $multiple_keys = get_option("kotacom_ai_{$provider}_api_keys", array());
            
            if (!empty($single_key) && empty($multiple_keys)) {
                update_option("kotacom_ai_{$provider}_api_keys", array($single_key));
                // Don't delete the old key yet for compatibility
            }
        }
    }
    
    /**
     * Debug method to check API key configuration
     */
    public function debug_api_keys($provider) {
        $keys = $this->get_provider_keys($provider);
        $active_index = get_option("kotacom_ai_{$provider}_active_key_index", 0);
        $active_key = $this->get_active_api_key($provider);
        $next_key = $this->get_next_available_key($provider);
        
        $debug_info = array(
            'provider' => $provider,
            'total_keys' => count($keys),
            'active_index' => $active_index,
            'active_key_length' => $active_key ? strlen($active_key) : 0,
            'next_key_length' => $next_key ? strlen($next_key) : 0,
            'keys_in_cooldown' => array()
        );
        
        // Check which keys are in cooldown
        for ($i = 0; $i < count($keys); $i++) {
            if ($this->is_key_in_cooldown($provider, $i)) {
                $debug_info['keys_in_cooldown'][] = $i;
            }
        }
        
        return $debug_info;
    }
}