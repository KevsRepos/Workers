<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>API Dokumentation</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f8fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 32px 40px;
        }
        h1 {
            color: #2d3748;
            font-size: 2.2rem;
            margin-bottom: 24px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 12px;
        }
        .accordion {
            width: 100%;
        }
        .accordion-item {
            background: #f1f5f9;
            border-radius: 8px;
            margin-bottom: 16px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
            overflow: hidden;
        }
        .accordion-header {
            cursor: pointer;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            font-size: 1.1rem;
            font-weight: 500;
            background: #e2e8f0;
            border: none;
            outline: none;
            transition: background 0.2s;
        }
        .accordion-header:hover {
            background: #cbd5e1;
        }
        .method {
            color: #fff;
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 4px;
            margin-right: 18px;
            font-size: 1rem;
            min-width: 60px;
            text-align: center;
            display: inline-block;
        }
        .method-get    { background: #38a169; } /* green */
        .method-post   { background: #4299e1; } /* blue */
        .method-put    { background: #ed8936; } /* orange */
        .method-delete { background: #e53e3e; } /* red */
        .method-patch  { background: #d53f8c; } /* pink */
        .method-any    { background: #718096; } /* gray */
        .path {
            color: #2b6cb0;
            font-size: 1.1rem;
            margin-right: 12px;
        }
        .name {
            color: #718096;
            font-size: 0.95rem;
        }
        .accordion-content {
            display: none;
            padding: 18px 20px;
            background: #fff;
            border-top: 1px solid #e2e8f0;
        }
        .accordion-content.open {
            display: block;
        }
        .example-title {
            font-weight: bold;
            color: #4299e1;
            margin-top: 8px;
            margin-bottom: 2px;
            font-size: 1.05rem;
        }
        .example-block {
            background: #edf2f7;
            border-radius: 6px;
            padding: 12px 16px;
            margin: 10px 0 0 0;
            font-size: 0.98rem;
            font-family: 'Fira Mono', 'Consolas', monospace;
            color: #2d3748;
            white-space: pre-wrap;
        }
        .api-interact {
            margin-top: 16px;
        }
        .api-interact textarea {
            width: 100%;
            min-height: 80px;
            font-family: 'Fira Mono', 'Consolas', monospace;
            font-size: 1rem;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            padding: 8px;
            margin-bottom: 8px;
            resize: vertical;
        }
        .api-interact button {
            background: #4299e1;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 8px 18px;
            font-size: 1rem;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
        }
        .api-interact button:hover {
            background: #2b6cb0;
        }
        .api-response {
            background: #fefcbf;
            border-radius: 6px;
            padding: 10px 14px;
            margin-top: 8px;
            font-size: 0.97rem;
            font-family: 'Fira Mono', 'Consolas', monospace;
            color: #744210;
            white-space: pre-wrap;
        }
        @media (max-width: 600px) {
            .container {
                padding: 16px 8px;
            }
            h1 {
                font-size: 1.4rem;
            }
            .accordion-header, .accordion-content {
                padding: 12px 8px;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var headers = document.querySelectorAll('.accordion-header');
            headers.forEach(function(header) {
                header.addEventListener('click', function() {
                    var content = header.nextElementSibling;
                    var isOpen = content.classList.contains('open');
                    document.querySelectorAll('.accordion-content').forEach(function(c) {
                        c.classList.remove('open');
                    });
                    if (!isOpen) {
                        content.classList.add('open');
                    }
                });
            });

            document.querySelectorAll('.api-interact').forEach(function(block) {
                var button = block.querySelector('button');
                var textarea = block.querySelector('textarea');
                var responseDiv = block.querySelector('.api-response');
                button.addEventListener('click', function() {
                    var url = button.getAttribute('data-url');
                    var method = button.getAttribute('data-method');
                    var body = textarea ? textarea.value : undefined;
                    responseDiv.textContent = 'Loading...';
                    var headers = {
                        'Content-Type': 'application/json'
                    };
                    var token = localStorage.getItem('jwt_token');
                    if (token) {
                        headers['Authorization'] = 'Bearer ' + token;
                    }
                    var fetchOptions = {
                        method: method,
                        headers: headers
                    };
                    if (method !== 'GET') {
                        fetchOptions.body = body;
                    }
                    fetch(url, fetchOptions)
                    .then(res => res.text())
                    .then(text => {
                        responseDiv.textContent = text;
                    })
                    .catch(err => {
                        responseDiv.textContent = 'Error: ' + err;
                    });
                });
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>API Routen</h1>
        <form id="login-form" style="background: #f0f4f8; border-radius: 6px; padding: 16px 18px; margin-bottom: 18px; display: flex; gap: 12px; align-items: flex-end; max-width: 500px;">
            <div style="flex:1;">
                <label for="login-email" style="font-weight:500; color:#2d3748;">Email</label><br>
                <input type="email" id="login-email" name="email" style="width:100%; padding:7px; border-radius:4px; border:1px solid #cbd5e1; margin-top:3px;">
            </div>
            <div style="flex:1;">
                <label for="login-password" style="font-weight:500; color:#2d3748;">Password</label><br>
                <input type="password" id="login-password" name="password" style="width:100%; padding:7px; border-radius:4px; border:1px solid #cbd5e1; margin-top:3px;">
            </div>
            <button type="submit" style="background:#4299e1; color:#fff; border:none; border-radius:4px; padding:8px 18px; font-size:1rem; cursor:pointer; font-weight:500;">Login</button>
        </form>
        <div id="login-message" style="margin-bottom:12px; color:#c53030;"></div>
        <div id="jwt-token-display" style="margin-bottom: 24px;"></div>
        <script>
        // Login form logic and JWT display
        document.getElementById('login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            var email = document.getElementById('login-email').value;
            var password = document.getElementById('login-password').value;
            var msgDiv = document.getElementById('login-message');
            msgDiv.textContent = '';
            fetch('/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username: email, password: password })
            })
            .then(res => res.json())
            .then(data => {
                if (data.token) {
                    localStorage.setItem('jwt_token', data.token);
                    document.getElementById('jwt-token-display').innerHTML = `
                        <div style="background: #e6fffa; color: #234e52; border-radius: 6px; padding: 12px 16px; font-size: 1rem; margin-bottom: 8px;">
                            <strong>Current JWT Token:</strong>
                            <div style="word-break: break-all; margin-top: 6px;">
                                ${data.token}
                            </div>
                        </div>
                    `;
                    msgDiv.style.color = '#2f855a';
                    msgDiv.textContent = 'Login successful!';
                } else {
                    localStorage.removeItem('jwt_token');
                    document.getElementById('jwt-token-display').innerHTML = "";
                    msgDiv.style.color = '#c53030';
                    msgDiv.textContent = 'Login failed.';
                }
            })
            .catch(() => {
                localStorage.removeItem('jwt_token');
                document.getElementById('jwt-token-display').innerHTML = "";
                msgDiv.style.color = '#c53030';
                msgDiv.textContent = 'Login error.';
            });
        });
        </script>
        <div class="accordion">
            <?php foreach ($routes as $route): ?>
                <div class="accordion-item">
                    <div class="accordion-header">
                        <?php
                            $method = strtoupper($route['methods'][0] ?? 'ANY');
                            $methodClass = 'method-'.strtolower($method);
                            if (!in_array($method, ['GET','POST','PUT','DELETE','PATCH'])) {
                                $methodClass = 'method-any';
                            }
                        ?>
                        <span class="method <?= $methodClass ?>"><?= htmlspecialchars($method) ?></span>
                        <span class="path"><?= htmlspecialchars($route['path']) ?></span>
                        <span class="name">(<?= htmlspecialchars($route['name']) ?>)</span>
                    </div>
                    <div class="accordion-content">
                        <?php if (!empty($route['controllerInfo'])): ?>
                            <?php if (!empty($route['controllerInfo']['requestExample'])): ?>
                                <div class="example-title">Request Example</div>
                                <div class="example-block">
                                    <?= json_encode($route['controllerInfo']['requestExample'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?>
                                </div>
                                <div class="api-interact">
                                    <textarea></textarea>
                                    <button data-url="<?= htmlspecialchars($route['path']) ?>" data-method="<?= htmlspecialchars($route['methods'][0] ?? 'POST') ?>">Send</button>
                                    <div class="api-response"></div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($route['controllerInfo']['responseExample'])): ?>
                                <div class="example-title">Response Example</div>
                                <div class="example-block">
                                    <?= json_encode($route['controllerInfo']['responseExample'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (in_array('GET', $route['methods'])): ?>
                            <div class="api-interact">
                                <button data-url="<?= htmlspecialchars($route['path']) ?>" data-method="GET">Request</button>
                                <div class="api-response"></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>