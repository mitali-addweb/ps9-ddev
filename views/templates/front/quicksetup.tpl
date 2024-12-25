{* Quicksetup Template - Fixed Version *}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quick Setup - T-Shirt eCommerce</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .content {
            padding: 40px;
        }
        .error-box {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            color: #721c24;
        }
        .info-box {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            color: #0c5460;
        }
        .steps {
            list-style: none;
            counter-reset: step-counter;
            margin: 20px 0;
        }
        .steps li {
            counter-increment: step-counter;
            padding: 15px 0 15px 50px;
            position: relative;
            border-bottom: 1px solid #dee2e6;
        }
        .steps li:before {
            content: counter(step-counter);
            position: absolute;
            left: 0;
            top: 15px;
            width: 35px;
            height: 35px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            font-weight: 600;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
        iframe {
            width: 100%;
            min-height: 600px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Quick Setup</h1>
            <p>T-Shirt eCommerce Designer Configuration</p>
        </div>

        <div class="content">
            {if isset($error) && $error}
                <div class="error-box">
                    <h3>⚠️ Cannot Access Quick Setup</h3>
                    <p>{$error}</p>
                </div>

                {if !$designer_app_exists}
                    <div class="info-box">
                        <h3>Designer Application Required</h3>
                        <p>Quick Setup requires the designer application to be installed.</p>
                    </div>

                    <h3>Installation Steps:</h3>
                    <ol class="steps">
                        <li>
                            <strong>Download the full T-Shirt eCommerce package</strong><br>
                            <small>Visit tshirtecommerce.com and download the complete package (not just the PrestaShop module)</small>
                        </li>
                        <li>
                            <strong>Extract the designer application</strong><br>
                            <small>Look for the <code>tshirtecommerce</code> folder in the package</small>
                        </li>
                        <li>
                            <strong>Copy to PrestaShop root</strong><br>
                            <small>Place at: <code>/opt/lampp/htdocs/prestashop_v9/tshirtecommerce/</code></small>
                        </li>
                        <li>
                            <strong>Set permissions</strong><br>
                            <small>Run: <code>chmod -R 777 data/ uploaded/</code></small>
                        </li>
                        <li>
                            <strong>Return to this page</strong><br>
                            <small>Quick Setup will then work</small>
                        </li>
                    </ol>

                    <a href="https://tshirtecommerce.com" class="btn" target="_blank">Visit T-Shirt eCommerce</a>
                    <a href="{$admin_link|default:'#'}" class="btn">Back to Module Settings</a>
                {else}
                    <script>
                        // Redirect to Designer Admin
                        window.location.href = '{$site_url}tshirtecommerce/admin/';
                    </script>
                    <div class="info-box" style="background: #d1ecf1; border-left-color: #17a2b8;">
                        <h3>🔄 Redirecting...</h3>
                        <p>Taking you to the Designer Admin panel...</p>
                        <p style="margin-top: 15px;">
                            If you are not redirected automatically,
                            <a href="{$site_url}tshirtecommerce/admin/">click here</a>.
                        </p>
                    </div>
                {/if}

            {else if isset($designer_app_exists) && $designer_app_exists}
                <div class="info-box">
                    <p>✅ Designer application detected. Loading Quick Setup...</p>
                </div>

                <iframe src="{$quicksetup_url}" id="quicksetup-frame"></iframe>

            {else}
                <div class="error-box">
                    <p>Unknown error. Please contact support.</p>
                </div>
            {/if}
        </div>
    </div>
</body>
</html>
