
## The Challenge

Ask Alexa a question about our site. Let Alexa interact with our site, and get
a response to any question it knows about.


## The Question

Ask Alexa: “Who is Ben Helmer and what are his hobbies”


## The NLP JSON

NLP will convert the question to a weighted object:

```
{
  "query" : [ {
    "word" : "helmer",
    "weight" : 4
  }, {
    "word" : "ben",
    "weight" : 4
  }, {
    "word" : "hobby",
    "weight" : 2
  } ],
  "required" : null,
  "path" : "/content/ey",
  "type" : "[nt:base]"
}
```

## The GET Request:

```
http://drupal.eyinnovation.com/api/ask?question=%7B%0A++%22query%22+%3A+%5B+%7B%0A++++%22word%22+%3A+%22helmer%22%2C%0A++++%22weight%22+%3A+4%0A++%7D%2C+%7B%0A++++%22word%22+%3A+%22ben%22%2C%0A++++%22weight%22+%3A+4%0A++%7D%2C+%7B%0A++++%22word%22+%3A+%22hobby%22%2C%0A++++%22weight%22+%3A+2%0A++%7D+%5D%2C%0A++%22required%22+%3A+null%2C%0A++%22path%22+%3A+%22%2Fcontent%2Fey%22%2C%0A++%22type%22+%3A+%22%5Bnt%3Abase%5D%22%0A%7D
```

## The Response:
```
{
  "value" : "Ben Helmer enjoys photography.",
  "status" : 0
}
```


### References:

 - https://www.eyinnovation.com/ - Existing Adobe implimentation.



## TODO
COMPLETE:
- x - NO TICKET -- Docker -- Setup Drupal with Docker.
- x - NO TICKET -- Drupal -- Setup Drupal Server with Composer.
- x - NO TICKET -- Docker -- Setup SOLR to interface with D8.
    - `Search API Module`, `Search API SOLR`, Copy SOLR conf from search API solr module.
- x - NO TICKET -- Docker -- SASS watch/compiling.
- x - NO TICKET -- Drupal -- Setup Devel
- x - NO TICKET -- Alexa -- Setup Tomcat and NLP App.
- x - NO TICKET -- SERVER -- Setup PROD server.
- x - NO TICKET -- Alexa -- Amazon config integration. Talk to NLP.
- x - NO TICKET -- Drupal -- Custom Module to search and return Alexa result.
- x - NO TICKET -- Pull Annas Changes
- x - NO TICKET -- Sample content from Jeff
- x - NO TICKET -- Theme -- Setup Drupal Theme.
- x - NO TICKET -- Lock down SOLR -- Password Protect? Route through Nginx?
- x - NO Ticket -- Lock down 8080
- x - No Ticket -- Lock down tomcat admin

IN PROGRESS:

BACKLOG:

BLOCKER:

CRITICAL:

MAJOR:
- NO TICKET -- Drupal -- CKEditor Plugin to mark Alexa content.

MINOR:
- NO TICKET -- Drupal -- Setup Drush
- NO TICKET -- SOLR -- Find config file for core definitions, and map as volume.


## Installation

Install (including new repo checkout):
`composer install`

Update:
`composer update` from the root directory.

Add Contrib Modules:
`composer require drupal/module_name 8.1.*@dev`


## SOLR

Requires: Search API, SOLR Search API.

__Use `solr` as the host__ when configuring Drupal to talk to SOLR.


## Working Questions:

About EY:

 - What is EY's Focus?
 - What acquisitions has EY made in digital?

HOME PAGE:

 - What does EY Innovation showcase? (Hints: showcase)
 - How many smart objects will there be by 2020?


## Working Questions not sent from Jeff:

- Tell me about NorthPoint's strength and experience.




## Alexa Questions from Adobe Version:

- Does EY provide digital technology assessments?
- How is the Adobe Marketing Cloud being used to drive personalization
- Tell me about the sharing economy business model.
- How are industrial mashups affecting the digital economy?
- What are some potentials of blockchain technology?
- How much money did EY make last year?
- Tell me about EY Society
- What is EY's Focus
- Tell me about NorthPoint's strength and experience.
- What are EY's Adobe analytics offerings?


- Who is In Koo Kim
- What is In Koo's number
- What is In Koo's email.
- Who is Jeff Penner
- What is Jeff Penner's email
- What is Jeff Penner's number
- Who is Greg Jenko
- What is Greg Jenko's number
- What is Greg Jenko's email
- What is EY innovation.
- Tell me about EY innovation.