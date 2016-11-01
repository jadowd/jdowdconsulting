/* $Id: README.txt,v 1.14 2008/10/15 02:31:03 sun Exp $ */

-- SUMMARY --

DrupalvB integrates vBulletin forums with Drupal.  It allows

- existing vBulletin users to log in to Drupal
- new vBulletin users to log in to Drupal
- existing Drupal users to log in to vBulletin (after initial export)
- new Drupal users to log in to vBulletin
- a single/shared sign-on when logging in via Drupal
- updating user data in vBulletin upon update in Drupal
- deleting users in vBulletin upon deletion in Drupal

Unlike vbDrupal (a fork of Drupal), DrupalvB turns Drupal's user-base into the
primary user-base (while still allowing existing or new vBulletin forum users
to logon with their user data in Drupal) and does not require patches to Drupal
core.

DrupalvB currently ships with 3 blocks:
- Recent threads/posts
- Forum users/posts/pm statistics for the current user
- Overall forum statistics (administrative)

Major parts of DrupalvB are based on my work for Migrator module.  If you
rather want to migrate an existing forum to Drupal (instead of integrating it),
you might want to checkout Migrator.


For a full description visit the project page:
  http://drupal.org/project/drupalvb
Bug reports, feature suggestions and latest developments:
  http://drupal.org/project/issues/drupalvb


-- REQUIREMENTS --

* vBulletin 3.6.x / 3.7.x


-- INSTALLATION --

* Install as usual, see http://drupal.org/node/70151 for further information.

* After installing vBulletin, copy config.php from your vBulletin includes/
  directory into DrupalvB's module folder.


-- CONFIGURATION --

* Configure DrupalvB's settings in administer >> Site configuration >> DrupalvB.
  Please note that you *must* supply a database connection, even if it is
  identical to Drupal's.

* Enable DrupalvB's blocks in administer >> Site building >> Blocks.


-- IMPLEMENTATION --

* To properly logout users from vBulletin and Drupal, you should replace the
  logout link in your vBulletin template with a link pointing to '/logout'
  (i.e. Drupal's logout URL), so the user is logged off from both systems.

* To login users concurrently in Drupal and vBulletin, you should remove the
  user login form in your vBulletin template and point users to Drupal's
  login page.


-- CUSTOMIZATION --

* If you want to run your forums on a subdomain, f.e. forums.example.com rather
  than example.com/forums, you need to use a common cookie domain for both
  domains.

  WARNING: Advanced users only. This may render your site unusable.

  In vBulletin, go to

    vBulletin Options >> Expand Setting Groups
    >> Cookies and HTTP Header Options >> Cookie Domain >> Edit Settings,

  select your domain without the subdomain (i.e. example.com), and save.
  In Drupal settings.php, find the section about cookie domain determination
  (around line 150), and un-comment the following line (replacing 'example.com'
  with your domain; without code tags):
<code>
$cookie_domain = 'example.com';
</code>


-- CONTACT --

Current maintainers:
* Daniel F. Kudwien (sun) - http://www.unleashedmind.com

This project has been sponsored by:
* UNLEASHED MIND
  Specialized in consulting and planning of Drupal powered sites, UNLEASHED
  MIND offers installation, development, theming, customization, and hosting
  to get you started. Visit http://www.unleashedmind.com for more information.

