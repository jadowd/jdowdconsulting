

The guitar module allows you to create chord diagrams. This can be done using either the input filter or the CCK field.

Input Filter method:

1. Download the module from drupal.org and unzip it in your modules directory.

2. Enable the "Guitar Chord Diagram Generator" and "Guitar Input Filter" 
   modules at http://example.com/admin/build/modules

3. Go to http://example.com/admin/settings/filters, and click on "configure" 
   next to the input format for which you want to enable chord diagrams.

4. Check the "Guitar chord filter" checkbox and click on the "Save configuration" button.

5. Click on the "configure" link again, and then choose the "Rearrange" tab.

6. Make sure that the "Guitar chord filter" is at the bottom of the list, and click on 
   the "Save configuration" button.

7. Create a new page at http://example.com/node/add/page

8. Make sure that the input format for which you enabled chord diagrams is selected.

9. In the page content, put the following notation: [C:x,3,2,0,1,0]

10. Save the page. You should now have the diagram for a C major chord.


CCK field method:

1. Download the module from drupal.org and unzip it in your modules directory.

2. Enable the "Guitar Chord Diagram Generator" and "Guitar CCK Field" 
   modules at http://example.com/admin/build/modules

3. Go to http://example.com/admin/content/types

4. Add a field to the content type of your choice. Choose "Guitar Chord" as the field type.

5. Set the field settings to your needs.

6. Create a new item of the content type to which you added your field. Set the "notes"
   field to "x,3,2,0,1,0" (without the quotation marks) and the "chord name" field to 
   "C major".

7. Save the page. You should now have the diagram for a C major chord.


Advanced settings:

Settings for the location of the image files and the diagram rendering can be 
set at http://example.com/admin/settings/guitar_diagram
