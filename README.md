# Database / Images Import

## Localhost & Heroku

- on localhost all's fine with the Wordpress Importer
- on Heroku Importer will time out and not upload images only posts and other data
- images can be uploaded manually but on server restart they will be lost
- images will be added manually and stored to Amazon AWS S3 via the WP Read Only plugin
- therefore on Heroku only a few posts will be shown
 




# Install

## Localhost & Heroku

- http://decielo.com/articles/350/wordpress-on-heroku-up-and-running
- "Having a local installation is key when running WP on Heroku because, alas, Heroku’s php doesn’t have zlib compiled. 
This means you need to do all plugin installations and updates from your local environment, and deploy the changes."
- on localhost: cd Sites; ln -s work/drop-o-folio drop-o-folio; create local db in mamp, edit wp-config ... and done
- granting permissions: http://ardeearam.com/solved-wordpress-asking-for-ftp-credentials-when-upgrading/ aka sudo chown -R www-data wordpress/
- permalinks in .htaccess are not compatible with localhost and heroku. in localhost the site is in a subfolder 'shopper' and on heroku it is in the '/'. uploading local .htaccess breaks heroku, or vice versa. the solution is to regenerate permalinks on heroku every time


