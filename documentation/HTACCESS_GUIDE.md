# .htaccess Configuration Guide

## Overview

The Dr. Feelgood application uses `.htaccess` files for URL rewriting to create clean, user-friendly URLs.

---

## File Locations

### Root `.htaccess`
**Location:** `/Users/apple/Silverwebbuzz/drfeelgoodapp/.htaccess`  
**Purpose:** Routes all requests from root to `public/index.php`

### Public `.htaccess`
**Location:** `/Users/apple/Silverwebbuzz/drfeelgoodapp/public/.htaccess`  
**Purpose:** Handles routing within the public folder

---

## How It Works

1. **User visits:** `https://app.drfeelgoods.in/`
2. **Root .htaccess intercepts** the request
3. **Routes to:** `public/index.php`
4. **Public/index.php** handles the routing to controllers and views

---

## URL Rewrite Rules

### Root Level Rewriting
```apache
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ public/index.php?path=$1 [QSA,L]
```

**Explanation:**
- `RewriteBase /` - Base directory for rewriting
- `!-f` - Don't rewrite if it's a real file
- `!-d` - Don't rewrite if it's a real directory
- `^(.*)$` - Match any request
- `public/index.php?path=$1` - Route to index.php with path parameter

---

## Deployment Configurations

### Scenario 1: Root Deployment
```
Domain: https://app.drfeelgoods.in/
Path: /home/silverwebbuzz_in/public_html/drfeelgoods.in/
RewriteBase: /
```

### Scenario 2: Subdirectory Deployment
```
Domain: https://domain.com/app/
Path: /home/user/public_html/app/
RewriteBase: /app/
```

### Scenario 3: Subfolder on VPS
```
Domain: https://domain.com/clinic/
Path: /home/user/public_html/clinic/
RewriteBase: /clinic/
```

---

## VPS Deployment Setup

### Step 1: Verify Apache Modules
```bash
# SSH into VPS
ssh root@your-vps-ip

# Check if mod_rewrite is enabled
apache2ctl -M | grep rewrite

# If not enabled, run:
a2enmod rewrite
```

### Step 2: Configure Virtual Host (if needed)
Edit Apache virtual host configuration:
```apache
<VirtualHost *:443>
    ServerName app.drfeelgoods.in
    DocumentRoot /home/silverwebbuzz_in/public_html/drfeelgoods.in

    <Directory /home/silverwebbuzz_in/public_html/drfeelgoods.in>
        AllowOverride All
        Require all granted
    </Directory>

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
</VirtualHost>
```

### Step 3: Restart Apache
```bash
systemctl restart apache2
```

---

## Testing the Setup

### Test 1: Root URL
```
Visit: https://app.drfeelgoods.in/
Expected: Login page loads
```

### Test 2: Direct File Access
```
Visit: https://app.drfeelgoods.in/public/index.php
Expected: Login page loads
```

### Test 3: Dashboard Route
```
Visit: https://app.drfeelgoods.in/dashboard
Expected: Dashboard page loads (after login)
```

### Test 4: Patient Route
```
Visit: https://app.drfeelgoods.in/patients
Expected: Patient list page loads (after login)
```

### Test 5: Patient Detail Route
```
Visit: https://app.drfeelgoods.in/patient/1
Expected: Patient profile page loads (after login)
```

---

## Troubleshooting

### Issue: 404 Errors on All Pages
**Solution:**
1. Verify Apache mod_rewrite is enabled
2. Check `.htaccess` has correct `RewriteBase`
3. Verify `.htaccess` file exists at root
4. Check file permissions: `chmod 644 .htaccess`
5. Verify `AllowOverride All` in virtual host config

### Issue: Blank Page / 500 Error
**Solution:**
1. Check Apache error logs: `tail -f /var/log/apache2/error.log`
2. Verify `public/index.php` is accessible
3. Check database connection in `config/database.php`
4. Verify PHP is installed and working

### Issue: CSS/JS/Images Not Loading
**Solution:**
1. Verify public files exist in correct location
2. Check file permissions on assets
3. Use absolute paths in HTML if needed
4. Verify MIME types are set correctly

### Issue: POST Requests Failing
**Solution:**
1. Verify `[QSA,L]` flags are in rewrite rule (preserves query strings)
2. Check form method is POST
3. Verify CSRF token is being passed (if implemented)

---

## Best Practices

✅ **Do:**
- Use relative URLs in templates
- Keep `.htaccess` simple and organized
- Test on staging before production
- Backup original `.htaccess` before changes
- Document any custom rewrite rules

❌ **Don't:**
- Remove critical rewrite rules
- Use absolute URLs (use relative)
- Deploy without testing
- Modify `.htaccess` on production directly

---

## For Local Development

If testing locally without Apache mod_rewrite:

### Option 1: PHP Built-in Server
```bash
cd /Users/apple/Silverwebbuzz/drfeelgoodapp/public
php -S localhost:8000
```

Visit: `http://localhost:8000/`

### Option 2: Enable mod_rewrite on XAMPP/MAMP
1. Open Apache config: `httpd.conf`
2. Uncomment: `LoadModule rewrite_module modules/mod_rewrite.so`
3. Restart Apache

---

## Advanced Configuration

### HTTP to HTTPS Redirect
```apache
RewriteCond %{HTTPS} !=on
RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
```

### Remove www from Domain
```apache
RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]
RewriteRule ^(.*)$ https://%1/$1 [R=301,L]
```

### Prevent Hotlinking
```apache
RewriteCond %{HTTP_REFERER} !^https://app.drfeelgoods.in
RewriteCond %{REQUEST_FILENAME} \.(jpg|jpeg|png|gif)$
RewriteRule ^(.*)$ - [F]
```

---

## Resources

- Apache mod_rewrite documentation: https://httpd.apache.org/docs/current/mod/mod_rewrite.html
- .htaccess tutorial: https://www.askapache.com/htaccess/
- RewriteRule flags: https://httpd.apache.org/docs/current/mod/mod_rewrite.html#rewriterule

---

**For support:** Refer to Apache error logs or contact your hosting provider.
