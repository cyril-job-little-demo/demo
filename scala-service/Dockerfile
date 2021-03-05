FROM hseeberger/scala-sbt:8u222_1.3.5_2.13.1

# create the dist app-1.0-SNAPSHOT and move it next to the Dockerfile
COPY app-1.0-SNAPSHOT /scalaapp

EXPOSE 9000

WORKDIR /scalaapp/bin

CMD ["./app"]