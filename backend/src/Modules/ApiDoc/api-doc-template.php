<?php
// Guard clauses: ensure template variables exist for static analyzers / composers.
// Prevent accidental rendering when PHP is invoked from CLI (e.g. during
// `php bin/console ...` or image build steps) which can pollute build logs.
if (PHP_SAPI === 'cli') {
    return;
}

// These should never change runtime behaviour when the app passes correct data,
// but prevent "undefined variable" notices from parsers and static checks.
if (!isset($routes) || !is_iterable($routes)) {
    $routes = [];
}
?>

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
            max-width: 1200px;
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

            // Angepasster Fetch-Handler: unterstützt Platzhalter-Eingaben (z.B. {id})
            document.querySelectorAll('.api-interact').forEach(function(block) {
                var button = block.querySelector('button');
                var textarea = block.querySelector('textarea');
                var responseDiv = block.querySelector('.api-response');

                function buildUrl(url) {
                    var inputs = block.querySelectorAll('[data-param]');
                    inputs.forEach(function(inp) {
                        var name = inp.getAttribute('data-param');
                        var val = (inp.value || '').trim();
                        if (val === '') {
                            throw new Error('Missing parameter: ' + name);
                        }
                        // Ersetze alle Vorkommen von {name} mit URL-encodiertem Wert
                        url = url.replace(new RegExp('\\{' + name + '\\}', 'g'), encodeURIComponent(val));
                    });
                    return url;
                }

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
                    if (method !== 'GET' && body !== undefined) {
                        fetchOptions.body = body;
                    }

                    try {
                        url = buildUrl(url);
                    } catch (e) {
                        responseDiv.textContent = e.message + ' — bitte Parameter ausfüllen.';
                        return;
                    }

                    // Neuer Fetch-Handler: prüfe Content-Type und/oder Text-Inhalt,
                    // rendere als HTML wenn es HTML ist, sonst sichere Text-/JSON-Ausgabe.
                    fetch(url, fetchOptions)
                    .then(function(res) {
                        return res.text().then(function(text) {
                            return {
                                status: res.status,
                                contentType: (res.headers.get('content-type') || '').toLowerCase(),
                                text: text
                            };
                        });
                    })
                    .then(function(obj) {
                        var ct = obj.contentType || '';
                        var text = obj.text || '';

                        // heuristische Erkennung von HTML:
                        var isHtml = ct.indexOf('text/html') !== -1 ||
                                     ct.indexOf('application/xhtml+xml') !== -1 ||
                                     /^\s*<\s*(!doctype|html|svg|[a-zA-Z]+)/i.test(text);

                        if (isHtml) {
                            // HTML-Antworten werden als HTML gerendert
                            responseDiv.innerHTML = text;
                            return;
                        }

                        // JSON-Erkennung und schönformatierte Ausgabe
                        var isJson = ct.indexOf('application/json') !== -1 || /^\s*[\{\[]/.test(text);
                        if (isJson) {
                            try {
                                var parsed = JSON.parse(text);
                                responseDiv.textContent = JSON.stringify(parsed, null, 2);
                            } catch (e) {
                                responseDiv.textContent = text;
                            }
                            return;
                        }

                        // Standard: sichere Text-Ausgabe
                        responseDiv.textContent = text;
                    })
                    .catch(function(err) {
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

        <!-- BIN -> UUID Konverter (neu) -->
        <div id="bin-to-uuid" style="background:#f8fafc;border-radius:6px;padding:12px 14px;margin-bottom:18px;max-width:500px;">
            <label style="font-weight:600;color:#2d3748;display:block;margin-bottom:6px;">BIN → UUID Konverter</label>
            <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;">
                <select id="b2u-type" style="padding:6px;border-radius:4px;border:1px solid #cbd5e1;">
                    <option value="hex">Hex (32 chars)</option>
                    <option value="base64">Base64 (16 bytes)</option>
                </select>
                <input id="b2u-input" placeholder="z. B. 4a7d... oder ABe...==" style="flex:1;padding:7px;border-radius:4px;border:1px solid #cbd5e1;font-family:monospace;">
                <button id="b2u-convert" style="background:#4299e1;color:#fff;border:none;border-radius:4px;padding:7px 12px;cursor:pointer;">Convert</button>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <div id="b2u-output" style="flex:1;background:#edf2f7;border-radius:6px;padding:10px;font-family:'Fira Mono',monospace;color:#2d3748;word-break:break-all;"></div>
                <button id="b2u-copy" style="background:#2b6cb0;color:#fff;border:none;border-radius:4px;padding:7px 12px;cursor:pointer;">Copy</button>
            </div>
            <div id="b2u-error" style="color:#c53030;margin-top:8px;"></div>
            <div style="margin-top:8px;font-size:0.92rem;color:#4a5568;">Unterstützt: Hex (32 hex digits) oder Base64 (kodierte 16 Bytes).</div>
        </div>
        <!-- /BIN -> UUID Konverter -->

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

        <script>
        // BIN -> UUID Converter JS (neu)
        (function() {
            var inp = document.getElementById('b2u-input');
            var typeSel = document.getElementById('b2u-type');
            var out = document.getElementById('b2u-output');
            var err = document.getElementById('b2u-error');
            var btn = document.getElementById('b2u-convert');
            var copyBtn = document.getElementById('b2u-copy');

            function bytesToHex(bytes) {
                return Array.prototype.map.call(bytes, function(b){
                    return ('0' + (b & 0xFF).toString(16)).slice(-2);
                }).join('');
            }
            function hexToUuid(hex) {
                hex = hex.replace(/[^0-9a-fA-F]/g,'').toLowerCase();
                if (hex.length !== 32) return null;
                return hex.substr(0,8) + '-' + hex.substr(8,4) + '-' + hex.substr(12,4) + '-' + hex.substr(16,4) + '-' + hex.substr(20,12);
            }
            function tryBase64ToHex(s) {
                try {
                    var bin = atob(s);
                    if (bin.length !== 16) return null;
                    var hex = '';
                    for (var i=0;i<bin.length;i++) {
                        hex += ('0' + bin.charCodeAt(i).toString(16)).slice(-2);
                    }
                    return hex;
                } catch (e) {
                    return null;
                }
            }

            btn.addEventListener('click', function() {
                err.textContent = '';
                out.textContent = '';
                var val = (inp.value || '').trim();
                if (!val) { err.textContent = 'Bitte Eingabewert angeben.'; return; }

                var uuid = null;

                if (typeSel.value === 'hex') {
                    var cleaned = val.replace(/[^0-9a-fA-F]/g,'');
                    if (cleaned.length === 32) {
                        uuid = hexToUuid(cleaned);
                    } else {
                        err.textContent = 'Ungültiges Hex: Erwartet 32 Hex-Zeichen (16 Bytes).';
                        return;
                    }
                } else if (typeSel.value === 'base64') {
                    var hex = tryBase64ToHex(val);
                    if (hex) {
                        uuid = hexToUuid(hex);
                    } else {
                        err.textContent = 'Ungültiges Base64 oder nicht 16 Bytes nach Decodierung.';
                        return;
                    }
                } else {
                    err.textContent = 'Unbekannter Typ.';
                    return;
                }

                if (!uuid) {
                    err.textContent = 'Konvertierung fehlgeschlagen.';
                    return;
                }

                out.textContent = uuid;
            });

            copyBtn.addEventListener('click', function() {
                var text = out.textContent || '';
                if (!text) return;
                navigator.clipboard ? navigator.clipboard.writeText(text) : (function(t){ var ta=document.createElement('textarea'); ta.value=t; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); ta.remove(); })(text);
            });
        })();
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
                                <?php
                                    // Parameter aus Pfad extrahieren
                                    preg_match_all('/\{([^}]+)\}/', $route['path'], $paramMatches);
                                    $hasParams = !empty($paramMatches[1]);
                                ?>
                                <div class="api-interact">
                                    <?php if ($hasParams): ?>
                                        <div class="param-inputs" style="display:flex;gap:8px;margin-bottom:8px;flex-wrap:wrap;">
                                            <?php foreach ($paramMatches[1] as $p): ?>
                                                <input data-param="<?= htmlspecialchars($p) ?>" placeholder="<?= htmlspecialchars($p) ?>" style="padding:6px;border-radius:4px;border:1px solid #cbd5e1;font-family:monospace;" />
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
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
                            <?php
                                preg_match_all('/\{([^}]+)\}/', $route['path'], $paramMatches2);
                                $hasParamsGet = !empty($paramMatches2[1]);
                            ?>
                            <div class="api-interact">
                                <?php if ($hasParamsGet): ?>
                                    <div class="param-inputs" style="display:flex;gap:8px;margin-bottom:8px;flex-wrap:wrap;">
                                        <?php foreach ($paramMatches2[1] as $p): ?>
                                            <input data-param="<?= htmlspecialchars($p) ?>" placeholder="<?= htmlspecialchars($p) ?>" style="padding:6px;border-radius:4px;border:1px solid #cbd5e1;font-family:monospace;" />
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
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