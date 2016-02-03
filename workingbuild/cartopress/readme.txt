=== Plugin Name ===
Contributors: Aaron Baideme, twarrior3dc
Donate link: http://troyhallisey.com
Tags: CartoPress, CartoDB, Leaflet, georeference, geocode, Google Maps, CartoCSS, mapping, cartodb, cartopress, map, cartography, sql, interactive map, maps
Requires at least: 4.3
Tested up to: 4.4
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

CartoPress allows you to geocode your WP site's posts and pages, and connect your data directly to CartoDB to create awesome interactive maps.

== Description ==
CartoPress links your WordPress site directly to CartoDB, an open-source and API-driven web-mapping platform. Geocode your posts, pages, and media and CartoPress will sync to your CartoDB account, allowing you to take advantage of CartoDB's visual editor and CartoCSS styling options.

**_One of a kind:_** While there are multitudes of other plugins providing mapping support, most are exclusively Google Maps-based instead of based on open-source solutions such as Leaflet; few are focused on geo-referencing your WordPress content; and CartoPress is the first to specifically target CartoDB. 

**_Your data, your maps, no hassle:_** CartoPress was built to provide the ability to turn your WordPress-based site into a geo-CMS using the CartoDB platform, allowing you to design dynamic interactive maps and geo-spatial appliations from your existing WordPress data. While CartoPress allows you to add robust geolocation to your WordPress posts, pages and media, CartoDB brings infinite possibilites of mapping and visualization. CartoPress makes use of CartoDB's powerful APIs to act as a bridge, enabling you to easily import your WordPress data directly into your CartoDB account. Add or update points on your map simply by adding or updating a post!

####KEY FEATURES
* Georeferece your posts, pages and media attachments using the built-in CartoPress geocoder
* Automatically sync Post Title, Summary Description, Permalink, Post Date, and all Geo Data to your CartoDB dataset
* Intuitive interface allows you to easily configure settings to sync Post Content, Featured Image, Taxonomies, Author, and Custom Fields to your CartoDB dataset
* Streamlined processes will automatically insert new posts, pages and media to your maps
* Provides the ability to manually input and edit and delete geodata, optionally choose to not sync data on a case-by-case basis
* Add a custom summary description to your post to display in your map's infowindow

####BENEFITS OF CARTODB
* Use CartoDB's intuitive visual editor to quickly and easily spatialize your WordPress data with wizards to make simple, clustered, choropleth and categorized maps, density and heat maps, time-based torque maps and more
* Customize your map's styling using CartoCSS, an end-user-friendly styling language based on CSS; choose from a variety of built-in basemaps or use your own custom basemap
* Create custom views based on your data using built-in SQL support
* Enhance your map by adding additional layers from the CartoDB dataset library or your own source
* Easily publish maps simply by sharing a link, embedding directly on your site, or build a custom geo-spatial application from your WordPress data using the cartodb.js API and Leaflet
* Use CartoDB to export your data into various geo datatypes including SHP, KML, GeoJSON, CSV and SVG
* CartoDB is an open-source platform and offers a free account providing a wide range of abilities for individual users, while upgraded accounts offer enterprise-ready solutions

== Installation ==

1. Installation is as simple as any other WordPress plugin. Just upload the zip file to the plugins directory in your WordPress site and ACTIVATE! Note: The latest stable release can be found in the WordPress plugin directory. For development versions, please see the Github [repo](https://github.com/MasterBaideme1021/cartopress).
2. Create a CartoDB account if you don't already have one.
3. Go to **Settings > CartoPress** and input your CartoDB API Key (available at yoursubdomain.cartodb.com/your_apps) and Username (your subdomain).
4. Create a unique name for your dataset in CartoDB and click the __'Connect to CartoDB'__ button. Your dataset will then be automatically created in your CartoDB account. Be sure to __'Save'__ changes on the settings page
5. Configure your options: Check the boxes for which post types you would like to geocode — Posts, Pages, and/or Media. Check any additional data sync options for syncing Post Content, Categories, Tags, Featured Image, Author, Post Format or Custom Fields

== Frequently Asked Questions ==

= How do I create a CartoDB account? =

You can create a free CartoDB account [here](https://cartodb.com/signup).

= How do I geocode my posts? =

The CartoPress Geocoder will appear on the edit screens for the post types that are checked in the settings. The easiest way to geocode is to input an address into the Search field and select one of the results. Note that not all of the geo-fields are required for syncing to CartoDB. At a minimum, only the latitude and longitude fields are required in order for the sync to work. A CartoDB ID will appear to the bottom-right of preview map when a post has been successfully synced to CartoDB.

= How do I manually input geo data? =

Checking the "Allow Geo Data Editing" box will unlock the geo fields for editing. This is useful for changing the Display Name or manually filling in fields that the Search does not. You can also manually edit the latitude and longitude fields.

= What is the mapping engine used by the Geocoder? =

The Geocoder search feature uses [Nominatim](http://wiki.openstreetmap.org/wiki/Nominatim), OpenStreetMap's open source georeferencer. Note that these results are not required to geocode your posts, but simply provides an easy way to obtain the required latitude and longitude information. You can also manually input this data.

= Do drafts and private posts sync to CartoDB? =

No, only posts with the status of 'Publish' will be synced. If you later change a published post to private, trash, draft or any other type, it will automatically be deleted from your CartoDB dataset.

= Can I choose to not sync a single post on a case-by-case basis? =

Yes, you can click the 'Do Not Sync' toggle to optionally choose to not sync a published post. You can find this by clicking on the Geocoder settings tool just to the right of the CartoDB ID in the Geocoder. Note that it is only necessary to use this button if you would like to keep geodata stored for the post, but not sync with CartoDB. Any post that does not contain latitude and longitude data will not sync anyway.

= How can I remove geodata from CartoDB, but keep my post published? =

The Geocoder settings toggle (located to the right of the CartoDB ID) contains a 'Delete Geo Data' button. Click this button remove all geodata from both CartoDB and geodata stored directly in the WordPress database. If you would like to prevent the post from re-syncing to CartoDB, either leave the latitude and longitude fields blank or use the 'Do Not Sync' button.

= Can I write a custom summary description for my infowindows? =

Yes, use the Summary Description textarea to write your own custom summary description. Alternatively, if you are using Post Excerpt, you can leave the Summary Description blank and the Post Exceprt will be used instead. If both the Post Excerpt and Summary Description are blank, a summary will be generated using the first 55 words of the Post Content.

= Why are there no custom fields appearing in the Select menu even though I have the Custom Fields box checked in the sync settings? =

You must have at least one custom field in use. Any custom field meta keys that are currently set will appear in the select menu.

= Will CartoPress automatically geocode my existing Published posts? =

Unfortunately no, CartoPress will only sync data that is added or edited after the plugin has been installed. You will have to manually enter at least latitude and longitude data for each of the existing posts in order to sync. If you have a lot of posta and geodata saved in a custom field or from another plugin, there may be some creative work-arounds for bulk syncing, but this would need to be taken on a case-by-case basis.

= Can I use an existing CartoDB dataset? =

Yes, though it is recommended to start fresh. You can input an existing tablename in the CartoPress Settings, and you will get a prompt asking you to confirm the use of an existing dataset. Note that CartoPress uses a standard set of column names in order to sync to CartoDB and by confirming the use of an existing table, CartoPress will add these standardized columns to your table if they do not already exist. If you plan to use an existing table, you should make sure that your column names match exactly the column names used by CartoPress. Please see the documentation for a listing of these names. Also note that the WordPress Post ID is the joining attribute that allows syncing to occur. If your existing dataset does not have a column of Post IDs, then it is more than likely better to use a new dataset from scratch instead.

= How do I actually make a map? =

CartoPress is a tool for linking your WordPress data to CartoDB. Therefore, CartoPress does not contain any publishing settings, and all map-making must be done with CartoDB. But no worries, its super easy. Just login to your CartoDB account, select your dataset. You should see all of your data in a table view. Click on the Map view and you will instantly see a simple visualization of your data. You can customize this by using the Wizards, CartoCSS, SQL, Infowindow settings in the panel to the right. You can also use this same panel to add additional layers. When you are ready, you can click the "Visualize" button to create the map, where you can continue to use the editor to added labels, change the basemap, and Publish your map.

= How do I embed a map on my site? =

The easiest way to do this is to use the 'embed' option in the CartoDB publishing options. This will automatically generate the code to insert the map into a post or a blank page on your site. You can share the map directly by using the 'share' publishing option. You can also get a little a crazy and use the CartoDB.js API to create awesome custom visualizations and applications with your data.

== Changelog ==

= 1.0 =
* First stable release

= 0.1.0 =
* First development version


== Upgrade Notice ==

= 1.0 =
First stable release

= 0.1.0 =
This is the first development version. Please upgrade to the latest stable release if you are using this version.
