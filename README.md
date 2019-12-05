# RatingBundle :star:

This Symfony bundle provides integration of a star rating system.

### Requirements

* PHP 7.2+
* MySQL 5.7.20+
* Symfony 4.3+

### :package: Installation

Install bundle with `Composer` dependency manager first by running the command:

`$ composer req oachoor/rating-bundle`

### Add routes

``` yaml
rating:
    resource: '@RatingBundle/Controller'
    type: annotation
```
 
### (Optional) Create your own Entities 

Entities doesn't fully meet your requirements?, then you can create yours based on [Rating](Entity/Rating.php) and [Vote](Entity/Vote.php).

### Resolve abstract Entities with RatingBundle or your custom ones

``` yaml
doctrine:
    orm:
        resolve_target_entities:
            RatingBundle\Model\AbstractVote: RatingBundle\Entity\Vote or AcmeRatingBundle\Entity\Vote
            RatingBundle\Model\AbstractRating: RatingBundle\Entity\Rating or AcmeRatingBundle\Entity\Rating
            Symfony\Component\Security\Core\User\UserInterface: RatingBundle\Entity\User or AcmeRatingBundle\Entity\User
```

### Define mapping Bundle

Make sure you have registered the Bundle that holds the Entities as following:

``` yaml
doctrine:
    orm:
        entity_managers:
            default:
                mappings:
                    RatingBundle: ~ or AcmeRatingBundle: ~
```

### Update database schema

``` bash
$ bin/console doctrine:schema:update --force --no-debug
```

### Using voting strategy

There are two strategies for rating, based on IP addresses or cookies. (both? feel free to contribute)

``` yaml
oa_rating:
    strategy: cookie (default "ip")
    cookie_name: your_custom_name
    cookie_lifetime: '+1 year'
```

### Templates customization 

Templates can be overridden in the `<your-project>/templates/bundles/RatingBundle/` directory, the new templates must use the same name and path (relative to `RatingBundle/Resources/views/`) as the original templates.

To override the `Resources/views/rating/rate.html.twig` template, create this template: `<your-project>/templates/bundles/RatingBundle/rate.html.twig`

### Usage

To see rating result for a Content (read-only mode), use the following twig code:

``` twig
{{ render( controller( 'RatingBundle:Rating:result', {'contentId': yourContentId} ) ) }}
```

Rating is based on Content, to enable voting for a Content use the following twig code:

``` twig
{{ render( controller( 'RatingBundle:Rating:vote', {'contentId': yourContentId} ) ) }}
```

### Example

A minimal [Template](Resources/views/rating/view.html.twig) that contains rating-call, javascripts and stylesheets.
