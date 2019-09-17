
# SRC Information

## Security Features
It is important that our website and server structure both support a secure connection where sensitive queries and user credentials can be safely communicated between client and server. 
- It is necessary that Sleepovers' final deployment of the webserver uses SSL (HTTPS) so that all data communication between client and server can be properly encrypted.
- No passwords will be stored on the database or on the server. A Hash and Salt value will be stored instead which can be compared with the computed Hash value that is calculated by using Argon2i (https://github.com/P-H-C/phc-winner-argon2/tree/master).
- To securely transfer username and password information, all login pages will use a php form to submit the information via POST and the requested script on the server will then retrieve that information for evaluation.
- On each page, a SESSION will be used to keep track of the currently logged-in user and prevent sending login information multiple times.

## Naming Conventions
- All variables, files, and classes will be use lowercase letters, numbers, and underscores
