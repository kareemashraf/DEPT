# DEPT Home Assignment (IMDB & Vimeo)
can be tested online on http://ec2-34-242-203-164.eu-west-1.compute.amazonaws.com/
<hr>

## Built With (Tech stack)

* php 7.1 with framework [symfony 4.3](https://symfony.com/) 
* [PHPUnit testing](https://phpunit.de/)
* [Postman](https://www.getpostman.com/) for API testing
* [jQuery](https://jquery.com/)
* [Bootstrap](https://getbootstrap.com/)
* [TMDB API](https://www.themoviedb.org)
* [Vimeo API](https://developer.vimeo.com/)
* [AWS](https://aws.amazon.com/)

<hr>


## API
The API is generally RESTFUL and returns results in JSON.

visit the API DOCS on /api/doc

http://ec2-34-242-203-164.eu-west-1.compute.amazonaws.com/api/doc

|HTTP | resource | Description |
| --- | --- | --- |
| GET | /api/trailer/{title} | get IMDB list of movies with the mentioned title with aggregated data from Vimeo |
| POST | /api/send/{email}/movie/{movie} | send an Email with a HTML page |


## Getting Started

Clone the project repository by running the command below 

```bash
git clone https://github.com/kareemashraf/DEPT.git
```

After cloning, run:

```bash
composer install
```

And finally, start the application:

```bash
bin/console server:run
```

and visit [http://localhost:8000/](http://localhost:8000/) to see the application in action. (also keep in mind that port 8000 could be different if busy)

#### running PHPUnit test

```bash
composer test
```



<hr>



## Assignment
https://www.deptagency.com/case-backend-developers/
