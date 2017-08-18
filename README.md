# Wiping Out CSRF

This is a companion repository that includes demos for Wiping Out CSRF. It's provides examples of common CSRF manifestations of CSRF and provides a proof of concept to demonstrate how they work.

## Setup
This repository is completely self contained and can run with any web sever that provides PHP support. The simplest way to stand this up is with the embedded development server that comes with PHP. Using whichever method you prefer install PHP then from the directory run `php -S127.0.0.1:8080`.

## Examples

The following three examples will demonstrate the three types of common CSRF attacks that can be performed. They attack a sample banking application forcing the victim to send money to the attacker. Once the directory is setup you must login to application by going to http://localhost:8080/login.php and using the credentials test:test. Browsing to any of the following examples will perform the CSRF attack and when you refresh the account.php page a transaction will be added. Click the reset button to reset the state back to a clean slate.

### Resource Inclusion

image.html is example of a CSRF attack that uses remote resource inclusion by adding the url to an image tag.

### XHR

xhr.html is an example of a sample CSRF payload that utilizes XHR to perform a request.

### Form

form.html is an example CSRF template that can be used for building an attacker controlled form that mimics the form the victim would be clicking. It's used to demonstrate a form based CSRF attack.

### Form and XHR Hooks

index.html includes a collection of various conditions that may be used for sending HTTP requests where the injected JavaScript payload would need to insert a token to the request. It is a test set for verifying that the tokens are included correctly and properly allow the request to complete.
