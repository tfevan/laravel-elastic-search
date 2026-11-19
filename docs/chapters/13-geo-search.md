# Chapter 13: Geo Search

## What you will learn

- The `geo_point` field type and the formats it accepts.
- Filtering by distance with `geo_distance` and sorting with `_geo_distance`.
- Using `post_filter` so a distance aggregation still sees every store.
- A second index (`stores`) next to `products`, managed by its own Elastic migration.

## Where the code lives

| Layer     | File                                                          |
|-----------|---------------------------------------------------------------|
| Model     | `app/Models/Store.php`                                        |
| Migration | `elastic/migrations/*_create_stores_index.php`                |
| Seeder    | `database/seeders/StoreSeeder.php` (45 stores in 15 Dhaka areas) |
| Service   | `app/Services/Search/GeoService.php`                          |
| Endpoint  | `GET /api/v1/geo?latitude=&longitude=&radius_km=&type=&q=`    |
| Page      | `resources/js/pages/chapters/Chapter13Geo.vue`                |

## Mapping

```php
$mapping->geoPoint('location');
```

and in the model:

```php
'location' => ['lat' => $this->latitude, 'lon' => $this->longitude],
```

Other accepted formats are `"lat,lon"` strings, `[lon, lat]` arrays (note the order) and geohashes.

## The request

```json
{
  "post_filter": { "geo_distance": { "distance": "3km", "location": { "lat": 23.7925, "lon": 90.4078 } } },
  "sort": [ { "_geo_distance": { "location": { "lat": 23.7925, "lon": 90.4078 }, "order": "asc", "unit": "km" } } ],
  "aggs": { "rings": { "geo_distance": { "field": "location", "origin": { "lat": 23.7925, "lon": 90.4078 }, "unit": "km", "ranges": [ { "to": 1 }, { "from": 1, "to": 3 } ] } } }
}
```

- `geo_distance` (query or filter): keeps documents within a radius.
- `_geo_distance` (sort): orders by distance and returns the distance in `sort` of every hit.
- `geo_distance` (aggregation): counts documents per distance ring.

The radius is a `post_filter`, so the rings count every store matching the text and type, not just those inside
the radius.

## Other geo queries

`geo_bounding_box` (inside a rectangle, for map viewports) and `geo_shape` (polygons and routes).

## Try it

1. Start from Gulshan with 3 km and compare the list with the radar.
2. Move to Uttara, then enter your own latitude and longitude.
3. Pick a store type and watch the rings change.
4. Open the request panel and find the three `geo_distance` usages.