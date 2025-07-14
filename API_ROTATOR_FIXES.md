# API Rotator Fixes - Generator Issues

## Issues Fixed

### 1. **Generator Can't Read API Key from Rotator Only**
**Problem**: When API keys are added only in rotator (not basic settings), generator fails to find them.

**Root Cause**: Inconsistency in API key retrieval methods and lack of debugging.

**Fix**: 
- Added comprehensive logging to track key retrieval
- Improved error messages to guide users
- Enhanced `get_provider_keys()` debugging

### 2. **API Keys Don't Rotate on Rate Limits**  
**Problem**: When hitting rate limits, API keys don't rotate to next available key.

**Root Causes**: 
- Inconsistent API key methods (`get_next_available_key` vs `get_active_api_key`)
- Insufficient rate limit error detection
- Poor rotation logic for keys in cooldown

**Fixes**:
- **Consistent Key Retrieval**: Always use `get_next_available_key()` 
- **Enhanced Error Detection**: Added more rate limit error patterns
- **Better Rotation Logic**: Skip keys in cooldown, find available keys
- **Comprehensive Logging**: Track rotation attempts and failures

## Files Modified

### 1. `includes/class-api-handler.php`
- Added debug logging for key retrieval
- Fixed inconsistent API key methods after rotation
- Enhanced error messages for better user guidance

### 2. `includes/class-api-key-rotator.php`  
- Added extensive logging to track rotation process
- Enhanced rate limit error detection (added 429, resource_exhausted, etc.)
- Improved rotation logic to skip keys in cooldown
- Added debug method `debug_api_keys()` for troubleshooting

### 3. `kotacom-ai-content-generator.php`
- Added AJAX endpoint `ajax_debug_api_keys` for diagnostics

## How to Test the Fixes

### Test 1: API Key Detection
1. **Add keys ONLY in API Rotator** (not basic settings)
2. **Try generating content**
3. **Check error logs** for key detection debugging

**Expected**: Should find and use rotator keys successfully.

### Test 2: Rate Limit Rotation
1. **Add multiple API keys** in rotator (minimum 2)
2. **Generate content rapidly** to trigger rate limits
3. **Check error logs** for rotation activity

**Expected**: Should rotate to next available key when rate limited.

### Test 3: Debug Information
Access debug endpoint via browser console:
```javascript
// Debug current provider
fetch(ajaxurl, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'action=kotacom_debug_api_keys&provider=google_ai'
}).then(r => r.json()).then(console.log);
```

## Error Log Monitoring

Enable WordPress debug logging in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

**Look for these log entries**:
- `KotacomAI API Handler: Using API key for provider:`
- `KotacomAI API Rotator: Rate limit detected, attempting rotation`
- `KotacomAI API Rotator: Successfully rotated API key`

## Rate Limit Error Patterns Added

The rotator now detects these additional error patterns:
- `429` (HTTP status code)
- `resource_exhausted` (Google API)
- `usage_limit_exceeded`
- `daily_limit_exceeded`
- `minute_quota_exceeded`
- `hour_quota_exceeded`
- `tokens_per_minute_exceeded`
- `concurrent_requests_exceeded`
- `bandwidth_limit_exceeded`

## Troubleshooting Guide

### Issue: "No API keys configured for this provider"
**Solution**: 
1. Check if keys are added in API Rotator page
2. Verify provider selection matches configured keys
3. Check error logs for key detection debugging

### Issue: Keys don't rotate on rate limits
**Solution**:
1. Ensure multiple keys are configured (minimum 2)
2. Check if error message contains rate limit patterns
3. Verify keys aren't all in cooldown
4. Monitor error logs for rotation attempts

### Issue: All keys seem to be in cooldown
**Solution**:
1. Wait for cooldown period (default 1 hour)
2. Add more API keys to rotator
3. Use different providers to distribute load

### Debug Commands
```javascript
// Check API key configuration
fetch(ajaxurl, {
    method: 'POST', 
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'action=kotacom_debug_api_keys&provider=google_ai'
}).then(r => r.json()).then(data => console.table(data.data));

// Test all keys for a provider
fetch(ajaxurl, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'}, 
    body: 'action=kotacom_test_all_keys&provider=google_ai'
}).then(r => r.json()).then(console.log);
```

## Production Considerations

### Disable Verbose Logging
For production, disable verbose debug logging by editing `includes/class-api-key-rotator.php`:
```php
private $debug_logging = false; // Disable verbose logging for production
```

### Performance Impact
The fixes add minimal overhead:
- Logging only when enabled
- Efficient key rotation logic
- Cached rotation statistics

## Best Practices

1. **Multiple Keys**: Add at least 3 API keys per provider
2. **Monitor Logs**: Regularly check error logs for rotation activity  
3. **Diversify Providers**: Use multiple AI providers to distribute load
4. **Test Setup**: Use debug endpoints to verify configuration
5. **Monitor Cooldowns**: Track which keys are in cooldown
6. **Disable Debug Logs**: Turn off verbose logging in production

The API rotator should now work reliably for both key detection and rotation! 🚀