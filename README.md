# Overview
* The services can be found in `go-service`, `ruby-service`, `scala-service`.
* An API gateway can be found in `php-api-gateway`. Its role is to secure the access by providing a rate limiting functionality. 
* The frontend can be found in `frontend`.

# Host
The services are deployed in Google Cloud Run (see the `Dockerfile` in each folder).
The API gateway and frontend are deployed on a Apache server in OVH Web Hosting.