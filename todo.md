# For Version 1.0

### JS
* Add the ability to select result from map pins, instead of results on right
* Integrate the new document.ready code for the admin panel from geocoder-helper.js into geocoder.js
* Clear all points on map from old search when conducting a new search, right now they remain

### General/Marketing
* Create documentation in Github **IN PROGRESS**
* Update plugin and Github readmes **IN PROGRESS**

----
# For Version 1.1

### PHP
* WP shortcodes for quick viz/map embedding
* Create a save feature for frequently used location data
* Add support for text translations
* Add support for custom post types
* Create filters for better integrating with other plugins (hidden custom fields)

### JS
* Add the ability click on the map to get coordinates
* Set a checkbox to lock all pins on map or unlock them

----
## Future Versions
* Add support for geolocating comments and users

----
## Completed
* ~~add support for custom fields (user should get a list of all custom fields and select which ones they would like to add)~~ **DONE yay!!!**
* ~~Display map of location on edit page load if geodata is available~~ **DONE**
* ~~When you hover over a point on the map, there is tool tip with the text "Hover Text" can we either get rid of that or add the display name/lat/long to it?~~ **DONE added location display name to Hover Text**
* ~~General code cleanup for the PHP classes~~ **DONE**
* ~~Re-insert to CartoDB if user untrashes/restores~~ **FIXED DONE**
* ~~verify account settings bug: When you change info in the fields, the green checkmark goes away, but comes back if you update the settings even without re-verifying. Need to rework the verify setting so that it updates after table creation~~ **cant reproduce**
* ~~Noticed there are a bunch of undefined variable warnings that need to be debugged before releasing~~ **fixed what I noticed**
* ~~Bulk edit bug: When a user modifies post data (exception being post status) using bulk edit the data will not sync until the post is updated again. I suspect because the sync function is run before the update function so the old data is being synced.~~ **FIXED problem was that post-format was being set after the save_post callback ran. fixed to manually set the post-format before the callback runs**
* ~~Cartodb sync bug: posts that have apostrophes in the tags, catergories, etc. do not sync because of an error~~ **FIXED added character escaping for more options**
* ~~When there is no cartodb data present, but there is geodata in postmeta, the fields are not populating with the postmeta data~~ **FIXED**
* ~~Cartodb fields are not clearing if a user updates the location to a new location that does not make use of all of the fields. i.e. if old address is 227 N 7 St, Williamsburg, New York, NY, United States, and simply picks Geneva Switzerland as the new location. The postmeta is correct, however the CartoDB fields would show 227 N 7 St, Williamsburg, Geneva, Geneva, Switzerland. This is because null fields are being removed from the update query. Should create a more complex verify script that checks to see if the value has been changed from the previous and clears out the fields in CartoDB if necessary~~ **FIXED**
* ~~Admin panel button shrink if the admin panel is open when a user performa a search and selecs an entry. It is because the button input values are clearing with the other inputs as a results of the geocoder.js process~~ **FIXED - changed the jQuery input selector from grandparent #cp-geo-values to parent #cp-geo-fields and it no longer interferes with the admin panel**
* ~~Add support for bulk actions (move to trash, restore from trash, and edit) and quick edit~~ **DONE note bug above**
* ~~Create a button to revert input fields to saved data if user changes their mind~~ **DONE**
* ~~Create a checkbox in the new admin panel that can choose to not sync that particular post~~ **DONE**
* ~~Added admin panel to geolocator to display the delete and reset buttons. Delete button is working, still need to get the reset button working~~ **DONE**
* ~~Fix custom data not saving when editing attachment~~ **DONE**
* ~~Change inputs to readonly when user selects a location~~ **DONE**
* ~~Show/results when user searches address and updates page~~ **DONE update post not applicable as it defaults to hidden now**
* ~~Delete from CartoDB when marks as private or reverts publish status to unpublished~~ **DONE**
* ~~Delete from CartoDB when user moves to trash~~ **DONE**
* ~~Hide results if open when user clicks Current Location button~~ **DONE**
* ~~Populate all the fields when user selects an address, but only show display name, lat and long in the results~~ **DONE**
* ~~Pull data from CartoDB instead of postmeta for geofields, create an alert to user prompting them to update the post of the geodata has been edited in cartodb manually and doesnt match the value saved in postmeta (only for geodata, post data should come from wp directly always meaning that all changes to post data in cartodb will always be lost on post update)~~ **DONE did not do the create alert because I dont think its necessary anymore**
* ~~Add a readonly field for CartoDB ID (thinking floated right on the same level as the edit data checkbox)~~ **DONE**
* ~~Display empty map with base layer only on edit page load if no geodata available~~ **DONE**
* ~~Fix responsive css~~ **DONE but there might be a couple of bugs I didn't catch**
* ~~Change color of input when user selects address to better match the look~~ **DONE - just left a border and removed background color and font styling**
* ~~Remove color permanently when user checks the edit fields button (right now the color returns when user un-checked the box)~~ **DONE - removes the ent class when user unchecks the button**
* ~~Ensure non-admin users can use geolocator (have to test, but i think its only available to admin users right now~~ **DONE this seems to be fine**
* ~~Add support for author sync~~ **DONE (note: uses Author display name, and will not change if user changes in the WP Dashboard, but will be reflected in future additions/updates**
