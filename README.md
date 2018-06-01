# RatingBundle :star:

### Requirements

* PHP 7.1+
* MySQL 5.7.20+
* Symfony 3.4+

### :package: Installation

Install bundle with `Composer` dependency manager first by running the command:

`$ composer require oachoor/rating-bundle`

### Register the bundle

Enable the bundle in `app\AppKernel.php` file.

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        // ...
        new RatingBundle\RatingBundle(),
    );
}
```

### Add routes

```yaml
rating:
    resource: '@RatingBundle/Controller'
    type: annotation
```
 
### (Optional) Create your own Entities 

Entities doens't fully meet your requirements?, then you can create yours based on [Rating](Entity/Rating.php) and [Vote](Entity/Vote.php).

### Resolve abstract Entities with RatingBundle or yours?:

``` yaml
doctrine:
    orm:
        resolve_target_entities:
            RatingBundle\Model\AbstractVote: RatingBundle\Entity\Vote or AcmeRatingBundle\Entity\Vote
            RatingBundle\Model\AbstractRating: RatingBundle\Entity\Rating or AcmeRatingBundle\Entity\Rating
            Symfony\Component\Security\Core\User\UserInterface: FOS\UserBundle\Model\User # Voter (Optional)
```

### Update mapping and database schema

Make sure you have registred the Bundle that holds the Entities as following:

``` yaml
doctrine:
    orm:
        entity_managers:
            default:
                mappings:
                    RatingBundle: ~ or AcmeRatingBundle: ~
```

``` bash
$ bin/console doctrine:schema:update --force
```

### Usage

To see rating result for a Content (read-only mode), use the following twig code:

``` twig
{{ render( controller( 'RatingBundle:Rating:result', {'contentId' : YOUR_CONTENT_ID} ) ) }}
```

Rating is based on Content, to enable voting for a Content use the following twig code:

``` twig
{{ render( controller( 'RatingBundle:Rating:vote', {'contentId' : YOUR_CONTENT_ID} ) ) }}
```

Voting is based on IP address, meaning that each computer or device can only vote once. 

### Bonus

A minimal [Template](Resources/views/rating/view.html.twig) that contains rating-call, javascripts and stylesheets.