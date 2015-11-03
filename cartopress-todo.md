# Stuff to fix
* ~~fix custom data not saving when editing attachment~~ **DONE**
* ~~change inputs to readonly when user selects a location~~ **DONE**
* show/results when user searches address and updates page
* ~~delete from CartoDB when marks as private or reverts publish status to unpublished~~ **DONE**
* delete from CartoDB when user moves to trash **NOT DONE - need to get the hook to work right**
* hide results if open when user clicks Current Location button
* populate all the fields when user selects an address, but only show display name, lat and long in the results
* create a button to revert input fields to saved data if user changes their mind --- need to use wp_ajax call for this i think
* pull data from CartoDB instead of postmeta for geofields, create an alert to user prompting them to update the post of the geodata has been edited in cartodb manually and doesnt match the value saved in postmeta (only for geodata, post data should come from wp directly always meaning that all changes to post data in cartodb will always be lost on post update)
* add a readonly field for CartoDB ID (thinking floated right on the same level as the edit data checkbox)
* display empty map with base layer only on edit page load if no geodata available
* display map of location on edit page load if geodata is available
* add the ability to move the point on the map to change the location
* fix responsive css
* ~~change color of input when user selects address to better match the look~~ **DONE - just left a border and removed background color and font styling**
* ~~remove color permanently when user checks the edit fields button (right now the color returns when user un-checked the box)~~ **DONE - removes the ent class when user unchecks the button**
* ensure non-admin users can use geolocator (have to test, but i think its only available to admin users right now_
* create documentation in github
* create marketing page on github.io page with download button and tutorial/demo


----
## Additional features to add in future:
* wp shortcodes for quick viz/map publishing
* add support for custom fields (user should get a list of all custom fields and select which ones they would like to add)
* create ability to save frequently used location data in a select field and use it
* add ability to change base layer for both the post preview and in shortcode for viz publish
* add support for more OSM features including language/translation, and sync of OSM place ids/types
* add support for wp localization translations
