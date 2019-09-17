# Sleepovers
Please use the provided folder structure for your scope & charter, design documenation, communications log, weekly logs and final documentation.    You are free to organize any additional folder structure as required by the project.

You can find a live deployment of the website during development here: http://cosc499.ok.ubc.ca/27307164/

Any push events to master will automatically deploy the latest build to the server. This server will not be live after the final deliverable.

## Client Liason
Everton Smith

## Technical Lead
Josh Henderson

## Integration Lead
Trevor Richard

### Project Management Method
Kanbum XP (Scrumban with Paired Programming)

### Trello 
 https://trello.com/invite/b/QZ4F0oY4/32384e18310e0544dd6af91d229cfa03/sleepovers

Test CD

## Email for Testing
Email Address: capstonesleepoversmail@gmail.com  
Password: capstone_UBCO-2019


# 6.0 Installation Details
The following steps should be performed after downloading the entire GitHub repository.

## 6.1 Creating the Database
The Sleepovers website uses a MySQL database to store all information listed on the website. To begin using the website a database must be created using the schema provided in the repository. The src/my_sql/folder contains the files that will be used for creating the database:
### MySQL Schema DDL: “src/my_sql/schema_ddl.sql”
contains the database structure that needs to be used for the Sleepovers website database. Import this file into your database in order to start.
### MySQL Test Instance: “src/my_sql/test_instance.sql”
was a file used for testing the functionality of the database on our local development environment. This file is not necessary for the functionality of the website but can be a good place to start in order to understand how the website works.
### MySQL Travis: “src/my_sql/travis.sql”
this was the file used by Travis CI to create a test database on the server to constantly test the database each time the master branch on the repository was updated.

## 6.2 Credentials
After the database has been created there are certain credential files that need to be updated with the correct information in order to use the files correctly. All of these files can be found in the src/folder on the main page of the repository.

### MySQL Credentials: “src/my_sql_cred.php”
this file contains the credentials that PHP will use to gain access to the database of your choice. The file contains four fields that need to be updated in order to allow the website to access the database:
- mysql_servername: Where the MySQL database is stored, either locally or on different server.
- mysql_dbname: The name of the database.  
- mysql_username: The username for gaining access to the database.
- mysql_password: The password for the particular user to access the database.

### PHPMailer Credentials: “src/mail/mail_cred.php”
this file contains the email and password used by the website to send emails for a wide range of functionality. In order to allow PHPMailer to send emails these credentials need to be changed (the email and password given will be deleted and unusable).
- mail_sender_email: This is the email that will be used to send emails using PHPMailer. 
- mail_sender_password: This is the password used for the mail_sender_email account email. 
### Web URL: “src/web_info/url.php”
this class contains the URL used on emails to redirect users to the Sleepovers page when they click the link.
- web_url: This is the URL that will be placed in emails to redirect to the Sleepovers page. This will need to be changed to the domain name for the website. 

## 6.3 Additional Changes
### Payment API:
The website was not built with any payment API but was designed to handle responses sent by an API. The page is setup to handle a confirmation response from an API, if the response is that payment has not been confirmed then the page will be prepared to handle that as well. Comments can be found in “pages/order_result” and “pages/place_order.php” on what changes need to be made.
### Domain Name:
The website will not be handed over with a domain name and this will be required for deployment. 
### HTTPS:
Once a domain name and server is set up, HTTPS certification must be implemented before users can safely and securely connect to the website. If this is not set up, users are more likely liable to have their information stolen.
