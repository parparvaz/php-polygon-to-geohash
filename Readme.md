# Polygon to Geohash Converter

## Description

This PHP script converts a polygon (defined by an array of latitude and longitude points) into 8-character Geohashes for grid points within the polygon. It efficiently handles memory usage by using PHP generators and prevents duplicate Geohashes using a hash-based array.

## Usage

### Input

Provide an array of polygon vertices where each vertex is an array `[latitude, longitude]`. For example:

```php
$polygon = [
    [35.7649122860889, 51.5164471400451],
    ...
];
 ```

## Running the Script
To run the script, save the provided PHP code to a file (e.g., polygon_to_geohash.php) and execute it using the PHP command line:
```php
php example.php
```
Make sure to adjust the $polygon variable with your own polygon vertices before running the script.

## Output
The script will output unique 8-character Geohashes for grid points within the provided polygon.

## Notes
* Adjust the GRID_RESOLUTION constant to control the granularity of the grid.
* This script is optimized for memory efficiency and performance using PHP generators and hash-based storage.

## License

The Polygon-to-GeoHash is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
