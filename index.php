<?php
require_once('common.php');
setcookie('CSRF-TOKEN', generateToken($password));
?>
  <script>
    (function () {
      try {
        XMLHttpRequest.prototype._open = XMLHttpRequest.prototype.open;
        XMLHttpRequest.prototype._send = XMLHttpRequest.prototype.send;
        XMLHttpRequest.prototype._setRequestHeader = XMLHttpRequest.prototype.setRequestHeader;
        XMLHttpRequest.prototype.open = function (type, url, async, username, password) {
          this._options = {
            type: type,
            url: url,
            async: async,
            username: username,
            password: password,
            crossOrigin: isCrossOrigin(url)
          }
          this._open.apply(this, arguments);
        };
        XMLHttpRequest.prototype.send = function () {
          // If this is a cross origin request we don't want to touch the headers
          // because we may not have permission to set the X-Request-With or
          // X-CSRF-Header per the Access-Control-Allow-Headers and we don't want
          // to break the request. It's also unclear as to whether the other origin
          // will even care. If they're running Prevoty on both hosts we can offer
          // CSRF protection on top of CORS instead of token based.
          if (!this._options.crossOrigin) {
            // This is required because calling setRequestHeader with the same name
            // will join the multiple values with a comma instead of replacing.
            // This can result in the X-Requested-With header having the value of,
            // "XMLHttpRequest, XMLHttpRequest" if there is a library such as
            // jQuery on top of us. This can result in breaking the server side
            // since it wont expect the multiple copies.
            if (!this.isRequestHeaderSet('X-Requested-With')) {
              this.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            }
            var tokenValue = getCookieValue('CSRF-TOKEN');
            // Only set if not null otherwise the sever wont be able to distinguish
            // between missing and incorrect
            if (tokenValue !== null) {
              this.setRequestHeader('X-CSRF-Header', tokenValue);
            }
          }

          this._send.apply(this, arguments);
        };
        XMLHttpRequest.prototype.setRequestHeader = function (name, value) {
          if (this._headers === undefined) {
            this._headers = [];
          }

          this._headers.push(name);
          this._setRequestHeader.apply(this, arguments);
        };
        XMLHttpRequest.prototype.isRequestHeaderSet = function (name) {
          if (this._headers === undefined) {
            return false;
          }

          for (var i = 0; i < this._headers.length; i++) {
            if (this._headers[i] === name) {
              return true;
            }
          }

          return false;
        };
      } catch (e) {
        // We're probably an older version of IE or other ancient browser that
        // doesn't have XHR support. Nothing we can do about that currently
      }

      function getCookieValue(name) {
        var matched = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
        return matched ? matched.pop() : null;
      }

      function submitHook(evt) {
        var target;
        if (evt.target !== undefined) {
          target = evt.target;
        } else if (evt.srcElement !== undefined) {
          target = evt.srcElement;
        } else {
          // no idea what's going on but bail out; we can't save it
          return;
        }

        // Walk up from our initial target looking for one of the delegate
        // elements we actually care about. Because of the browser support matrix
        // we don't get to use fancy css selectors here so we need to limit it
        // just to specific element names. This is to support developers using
        // elements with JavaScript that they shouldn't to submit forms. This has
        // the possibility of matching a lot of false positives (eg. click events
        // for non-submit causing elements) but it shouldn't break anything
        // because we're always placing the token in the same place in the form.
        // It can just cause the browser to do some extra work.
        while (target !== null) {
          if (target.nodeName === 'A' || target.nodeName === 'INPUT' || target.nodeName === 'BUTTON') {
            break;
          }

          target = target.parentNode;
        }

        // We didn't find any of the delegates, bail out
        if (target === null) {
          return;
        }

        // If it's an input element make sure it's of type submit
        var type = target.getAttribute('type');
        if (target.nodeName === 'INPUT' && (type === null || !type.match(/^submit$/i))) {
          return;
        }

        // Walk up the DOM to find the form
        var form;
        for (var node = target; node !== null; node = node.parentNode) {
          if (node.nodeName === 'FORM') {
            form = node;
            break;
          }
        }

        if (form === undefined) {
          return;
        }

        var token;
        if (document.querySelector !== undefined) {
          token = form.querySelector('input[name="csrf_token"]');
        } else {
          var children = form.children;
          for (var i = 0; i < children.length; i++) {
            if (children[i].name === 'csrf_token') {
              token = children[i];
            }
          }
        }

        var tokenValue = getCookieValue('CSRF-TOKEN');
        if (token !== undefined && token !== null) {
          if (token.value !== tokenValue) {
            token.value = tokenValue;
          }

          // token already exists, we're done
          return;
        }

        var newToken = document.createElement('input');
        newToken.setAttribute('type', 'hidden');
        newToken.setAttribute('name', 'csrf_token');
        newToken.setAttribute('value', tokenValue);
        form.appendChild(newToken);
      }

      // We bind to the click event because IE doesn't bubble up submit and to
      // support delegation for other elements.
      if (document.addEventListener === undefined) {
        document.attachEvent('onclick', submitHook);
      } else {
        // Need to use the 3 parameter version for older Firefox otherwise it
        // breaks
        document.addEventListener('click', submitHook, false);
      }

      function isCrossOrigin(url) {
        return (url.match(/^\/\/|^https?:\/\//) !== null);
      }
    })();
  </script>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CSRF Demo</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/jquery-1.12.4.min.js"></script>
  </head>

  <body>
    <div class="row col-md-6 col-md-offset-3">
      <div class="panel panel-primary">
        <div class="panel-heading">Normal form</div>
        <div class="panel-body" id="test-1-body">
          <form id="test-1" method="POST" action="do.php">
            <input type="hidden" name="username" value="test" />
            <input type="submit" class="btn btn-primary" />
          </form>
          <pre id="test-1-code"></pre>
        </div>
        <script>
          $(document).ready(function () {
            $('#test-1-code').text($('#test-1-body>')[0].outerHTML.replace(/          ([^ ])/g, '$1'));
          });
        </script>
      </div>
    </div>

    <div class="row col-md-6 col-md-offset-3">
      <div class="panel panel-primary">
        <div class="panel-heading">Mock validation, preventDefault, re-submit bubble off</div>
        <div class="panel-body" id="test-2-body">
          <form id="test-2" method="POST" action="do.php">
            <input type="hidden" name="username" value="test" />
            <input type="submit" class="btn btn-primary" />
            <script>
              $(document).ready(function () {
                $(document).on('submit', '#test-2', function (evt) {
                  evt.preventDefault();
                  if (evt.target === undefined) {
                    evt.srcElement.submit();
                  } else {
                    evt.target.submit();
                  }
                  return false;
                });
              });
            </script>
          </form>
          <pre id="test-2-code"></pre>
        </div>
        <script>
          $(document).ready(function () {
            $('#test-2-code').text($('#test-2-body>')[0].outerHTML.replace(/          ([^ ])/g, '$1'));
          });
        </script>
      </div>
    </div>

    <div class="row col-md-6 col-md-offset-3">
      <div class="panel panel-primary">
        <div class="panel-heading">Mock validation, preventDefault, re-submit bubble on</div>
        <div class="panel-body" id="test-3-body">
          <form id="test-3" method="POST" action="do.php">
            <input type="hidden" name="username" value="test" />
            <input type="submit" class="btn btn-primary" />
            <script>
              $(document).ready(function () {
                $(document).on('submit', '#test-3', function (evt) {
                  evt.preventDefault();
                  if (evt.target === undefined) {
                    evt.srcElement.submit();
                  } else {
                    evt.target.submit();
                  }
                  return true;
                });
              });
            </script>
          </form>
          <pre id="test-3-code"></pre>
        </div>
        <script>
          $(document).ready(function () {
            $('#test-3-code').text($('#test-3-body>')[0].outerHTML.replace(/          ([^ ])/g, '$1'));
          });
        </script>
      </div>
    </div>

    <div class="row col-md-6 col-md-offset-3">
      <div class="panel panel-primary">
        <div class="panel-heading">preventDefault submit with xhr</div>
        <div class="panel-body" id="test-4-body">
          <form id="test-4" method="POST" action="do.php">
            <input type="hidden" name="username" value="test" />
            <input type="submit" class="btn btn-primary" />
            <script>
              $(document).ready(function () {
                $(document).on('submit', '#test-4', function (evt) {
                  evt.preventDefault();
                  var xhr = new XMLHttpRequest();
                  xhr.open('GET', '/do.php', true);
                  xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                      $('#test-4').css({ 'background-color': 'green' });
                    } else {
                      $('#test-4').css({ 'background-color': 'red' });
                    }
                  }
                  xhr.send("username=test");
                  return false;
                });
              });
            </script>
          </form>
          <pre id="test-4-code"></pre>
          <script>
            $(document).ready(function () {
              $('#test-4-code').text($('#test-4-body>')[0].outerHTML.replace(/          ([^ ])/g, '$1'));
            });
          </script>
        </div>
      </div>

    <div id="test-5">
      <p>non-form button xhr</p>
      <button id="test-5-submit-button">submit</button>
      <script>
        $(document).ready(function () {
          $(document).on('click', '#test-5-submit-button', function (evt) {
            evt.preventDefault();
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '/do.php', true);
            xhr.onreadystatechange = function () {
              if (xhr.readyState === 4 && xhr.status === 200) {
                $('#test-5').css({ 'background-color': 'green' });
              } else {
                $('#test-5').css({ 'background-color': 'red' });
              }
            }
            xhr.send("username=test");
            return false;
          });
        });
      </script>
    </div>

    <div>
      <p>Button submit form</p>
      <form id="test-6" method="POST" action="do.php">
        <input type="hidden" name="username" value="test" />
        <button id="test-6-submit-button">Submit</button>
      </form>
      <script>
        $(document).ready(function () {
          $(document).on('click', '#test-6-submit-button', function (evt) {
            evt.preventDefault();
            $('#test-6').submit();
            return false;
          });
        });
      </script>
    </div>

    <div id="test-7">
      <p>js inserted form</p>
      <button onclick="buildForm()">Add Form</button>
      <script>
        function buildForm() {
          var form = document.createElement('form');
          form.setAttribute('method', 'POST');
          form.setAttribute('action', '/do.php');

          var input = document.createElement('input');
          input.setAttribute('type', 'text');
          input.setAttribute('name', 'username');
          form.appendChild(input);

          var submit = document.createElement('input');
          submit.setAttribute('type', 'submit');
          form.appendChild(submit);

          $('#test-7').append(form);
        }
      </script>
    </div>
    <div>
      <p>js form submit from anchor tag</p>
      <form id="test-8" method="POST" action="do.php">
        <input type="hidden" name="username" value="test" />
        <a href="#" id="test-8-submit" class="btn btn-primary"><span>Submit</span></a>
      </form>
      <script>
        $(document).ready(function () {
          $(document).on('click', '#test-8-submit', function (evt) {
            evt.preventDefault();
            $('#test-8').submit();

            return false;
          });
        });
      </script>
    </div>
    <script src="js/bootstrap.min.js"></script>
  </body>
  </html>