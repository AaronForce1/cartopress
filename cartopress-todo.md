# Stuff to fix

* re-insert to CartoDB if user untrashes/restores **WORKING only when user selects Restore from the trash and from the Undo action**
* add support for bulk actions (move to trash, restore from trash, and edit) and quick edit
* add the ability to select result from map pins, instead of results on right
* create a button to revert input fields to saved data if user changes their mind --- need to use wp_ajax call for this i think
* display map of location on edit page load if geodata is available
* add the ability to move the point on the map to change the location
* set a checkbox to lock all pins on map or unlock them
* clear all points on map from old search when conducting a new search, right now they remain
* perform general code cleanup for the PHP classes
* create documentation in github **IN PROGRESS**
* create marketing page on github.io page with download button and tutorial/demo

----
## Stuff that is done
* ~~fix custom data not saving when editing attachment~~ **DONE**
* ~~change inputs to readonly when user selects a location~~ **DONE**
* ~~show/results when user searches address and updates page~~ **DONE update post not applicable as it defaults to hidden now**
* ~~delete from CartoDB when marks as private or reverts publish status to unpublished~~ **DONE**
* ~~delete from CartoDB when user moves to trash~~ **DONE**
* ~~hide results if open when user clicks Current Location button~~ **DONE**
* ~~populate all the fields when user selects an address, but only show display name, lat and long in the results~~ **DONE**
* ~~pull data from CartoDB instead of postmeta for geofields, create an alert to user prompting them to update the post of the geodata has been edited in cartodb manually and doesnt match the value saved in postmeta (only for geodata, post data should come from wp directly always meaning that all changes to post data in cartodb will always be lost on post update)~~ **DONE did not do the create alert because I dont think its necessary anymore**
* ~~add a readonly field for CartoDB ID (thinking floated right on the same level as the edit data checkbox)~~ **DONE**
* ~~display empty map with base layer only on edit page load if no geodata available~~ **DONE**
* ~~fix responsive css~~ **DONE but there might be a couple of bugs I didn't catch**
* ~~change color of input when user selects address to better match the look~~ **DONE - just left a border and removed background color and font styling**
* ~~remove color permanently when user checks the edit fields button (right now the color returns when user un-checked the box)~~ **DONE - removes the ent class when user unchecks the button**
* ~~ensure non-admin users can use geolocator (have to test, but i think its only available to admin users right now~~ **DONE this seems to be fine**
* ~~add support for author sync~~ **DONE (note: uses Author display name, and will not change if user changes in the WP Dashboard, but will be reflected in future additions/updates**

----
## Additional features to add in future:
* wp shortcodes for quick viz/map publishing
* add support for custom fields (user should get a list of all custom fields and select which ones they would like to add)
* create ability to save frequently used location data in a select field and use it
* add ability to change base layer for both the post preview and in shortcode for viz publish
* add support for more OSM features including language/translation, and sync of OSM place ids/types
* add support for wp localization translations

