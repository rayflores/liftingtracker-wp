# 🚨 LiftingTracker Pro - Registration Link Fix Guide

## Problem: Registration link not working on live server

The `/register` and `/login` URLs return 404 errors after uploading to live server.

## 🔧 Quick Fix Solutions

### **Solution 1: Flush Permalink Structure (Recommended)**

1. **Login** to your WordPress admin panel
2. **Go to** `Settings > Permalinks`
3. **Click** "Save Changes" (don't change anything, just save)
4. **Test** the registration link: `yoursite.com/register`

### **Solution 2: Re-activate the Theme**

1. **Go to** `Appearance > Themes`
2. **Activate** a different theme (like Twenty Twenty-Five)
3. **Activate** LiftingTracker Pro again
4. **Test** the registration link

### **Solution 3: Check Permalink Structure**

1. **Go to** `Settings > Permalinks`
2. **Select** "Post name" structure
3. **Save Changes**
4. **Test** the registration link

## 🔍 Troubleshooting Steps

### **Step 1: Check if WordPress Registration is Enabled**

1. **Go to** `Settings > General`
2. **Check** "Membership" section
3. **Enable** "Anyone can register" if not already enabled
4. **Save Changes**

### **Step 2: Test Direct Links**

Try these URLs in your browser:
- `yoursite.com/register` (should show registration form)
- `yoursite.com/login` (should show login form)

### **Step 3: Check for Plugin Conflicts**

1. **Deactivate** all plugins temporarily
2. **Test** the registration link
3. **If it works**, reactivate plugins one by one to find the conflict

### **Step 4: Verify .htaccess File**

Check if your `.htaccess` file has WordPress rewrite rules:

```apache
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
```

## 🆘 Advanced Fixes

### **Fix 1: Manual Rewrite Rule Flush**

Add this code to your `functions.php` temporarily:

```php
// Add this at the end of functions.php (remove after 24 hours)
add_action('init', function() {
    if (isset($_GET['flush_rewrite'])) {
        flush_rewrite_rules();
        wp_die('Rewrite rules flushed! <a href="'.home_url().'">Go back to site</a>');
    }
});
```

Then visit: `yoursite.com/?flush_rewrite=1`

### **Fix 2: Check Server Configuration**

Some hosting providers don't support custom rewrite rules. Contact your host if:
- You've tried all solutions above
- Other WordPress sites work fine
- Only custom URLs don't work

### **Fix 3: Alternative Registration URL**

If nothing works, you can use WordPress default registration:
- `yoursite.com/wp-login.php?action=register`

## 🛡️ Server Environment Checks

### **Hosting Requirements:**
- ✅ **Apache/Nginx** with mod_rewrite enabled
- ✅ **WordPress** 6.0+ 
- ✅ **PHP** 8.0+
- ✅ **MySQL** 5.7+

### **Common Hosting Issues:**
- **Shared hosting** may have restrictions on rewrite rules
- **Cached sites** need cache clearing after changes
- **CDN services** may cache 404 errors

## 📞 Get Help

### **If registration link still doesn't work:**

1. **Check error logs** in cPanel or hosting control panel
2. **Contact hosting support** about rewrite rule issues
3. **Enable WordPress debug mode** to see detailed errors

### **WordPress Debug Mode:**

Add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Check `/wp-content/debug.log` for errors.

## ✅ Success Indicators

**Your registration link is working when:**
- ✅ `yoursite.com/register` shows the multi-step registration form
- ✅ `yoursite.com/login` shows the custom login form
- ✅ No 404 errors when visiting these URLs
- ✅ Form submission works correctly

## 📋 Prevention for Next Time

To avoid this issue in future uploads:

1. **Always flush permalinks** after theme upload
2. **Test custom URLs** immediately after activation
3. **Keep a backup** of working .htaccess file
4. **Document custom rewrite rules** for your hosting provider

---

**Need immediate help?** Most issues are resolved by simply going to `Settings > Permalinks` and clicking "Save Changes"! 🚀
