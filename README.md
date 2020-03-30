Weather Application 

How to install?

1. After cloning the repository into a folder, you can run these commands to start the application
- cd/<folder>
- docker-compose up 

2. It will create 3 containers
- mysql
- phalcon_sample_admin
- app

3. After installation
- mysql script should be run for creating tables and sample data.
- You can open Adminer at http://localhost:5000
- host:mysql
- user:root
- password:root
- db:phalcon_app
- Then you can run the sql script (sql script is available in root folder "phalcon_app.sql") in adminer.

API Endpoints

1. http://localhost:8085/users/register
- Request : POST (Body, form-data)
- Parameters : 
- email : string 
- password : string 
- city_id : integer (1 to 10, city list is available in mysql cities table )
- language : string
 


2. http://localhost:8085/users/login
- Request : POST (Body, form-data)
- Parameters : 
- email : string 
- password : string
 


3. http://localhost:8085/users/update
- Request : POST (Body, form-data)
- Header : authToken (It will be provided after login)
- Parameters : 
- email : string 
- password : string 
- city_id : integer (1 to 10, city list is available in mysql cities table )
- language : string
 


4. http://localhost:8085/users/activate
- Request : POST (Body, form-data)
- Header : authToken (It will be provided after login)
- Parameters : 
- id : integer (user id)
- promotionCode : string (Codes are available in mysql promotion_codes table.)
 
