Moodle 1.9 Contact Form Block by Daniele Cordella

Originally based on:
Moodle Web site Contact Form v_5 (by Nicole B. Hansen) (converted to a moodle block by Daryl Hawes)
Discussion at:
http://moodle.org/mod/forum/discuss.php?d=12411

This block has had many contributers, from Daryl Hawes in Moodle 1.5, Daniele Cordella in Moodle 1.6, Matt Campbell in Moodle 1.7 & 1.8, and Valery Fremaux in Moodle 1.8.

The Moodle 1.9 Contact Form Block builds upon the work of all these contributers and adds many new features made possible in 1.9.

This block:

1) Links via an html link or form button to a page where the user can submit comments.
2) Supports different behavior whether the block is displayed on the main site index or in a course.
3) Includes global configuration options.
4) Includes per block instance configuration options.
5) Utilizes two roles, block/contact_form:contactperson and block/contact_form:hiddenrecipient.
6) Provides ReCAPTCHA support for sites that have this configured.

This block is NOT compatible with Moodle versions prior to 1.9.
 
HOW TO INSTALL 

1) Copy the entire contact_form folder from the blocks folder in the package into your Moodle site's blocks folder
2) Visit the notifications page of your Moodle site with your browser.  This is at Site Admin->Notifications, or you may go to http://YOURMOODLESITE/admin/index.php.

Specific changes from previous versions:

1) Converted block to use the Moodle formslib library.
2) Added reCaptcha support.  To use ReCAPTCHA, you must get a key and configure it on your Moodle install.  See http://docs.moodle.org/en/Manage_authentication#ReCAPTCHA for details on enabling ReCAPTCHA.
3) Added option for return receipts.
4) Removed previous methods of determining course teachers and replaced with a role.  block/contact_form:contactperson is enabled in the Teacher legacy role by default.
5) Removed previous methods of adding a hidden recipient and replaced with a role.  block/contact_form:hiddenrecipient is not enabled for any role when initially installed.