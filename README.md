# Statamic Curated Collection

> Statamic Curated Collection is a Statamic addon.

## Features

This addon does:

- This
- And this
- And even this

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

``` bash
composer require tv2regionerne/statamic-curated-collection
```

## How to Use

Here's where you can explain how to use this wonderful addon.



## API

This add-on integrates with the [Private API addon](https://statamic.com/addons/tv2reg/private-api) to provide an end point for managing handlers. The following endpoints are available:

Viewing all curated collections:
`GET {base}/statamic-curated-collections`

View an individual curated collection:
`GET {base}/statamic-curated-collections/{id}`

Add a new curated collection:
`POST {base}/statamic-curated-collections`

Update an individual curated collection:
`PATCH {base}/statamic-curated-collections/{id}`

Delete a curated collection:
`DELETE {base}/statamic-curated-collections/{id}`


View all individual curated collection entries:
`GET {base}/statamic-curated-collections/{id}/entries`

Reorder an invidividual curated collection entries:
`POST {base}/statamic-curated-collections/{id}/entries/reorder`

Add a new curated collection entry:
`POST {base}/statamic-curated-collections/{id}/entries`

View an individual curated collection entry:
`GET {base}/statamic-curated-collections/{id}/entries/{entryId}`

Update an individual curated collection entry:
`PATCH {base}/statamic-curated-collections/{id}/entries/{entryId}`

Delete a curated collection entry:
`DELETE {base}/statamic-curated-collections/{id}/entries/{entryId}`

