# Pretty template index
A really quick and dirty PHP 5.4 script to display (and link to) a listing of HTML pages/templates in a directory using a (I hope) pleasant looking table layout, rather than the usual Nginx [autoindex](http://wiki.nginx.org/HttpAutoindexModule) or Apache's [mod_autoindex](http://httpd.apache.org/docs/2.4/mod/mod_autoindex.html) - which I keep compiled out/disabled by default to avoid possible prying eyes.

Pulls out the page `<title>` from each template file if possible, for display in each table row item.

Typically what I will use when sharing template mock-ups/progress with clients during the development phase of a web application project.

View an [example here](http://magnetikonline.github.io/prettytemplateindex/).

## Usage
- Copy or symlink `index.php` into the directory with your HTML template files, or...
- If using Nginx, you could use/customise [nginx.example.conf](nginx.example.conf) as a way to enable the index page on a per virtual-host/directory basis without actually copying `index.php` around or creating symlinks. The script will determine the directory based on `DOCUMENT_ROOT`/`DOCUMENT_URI`. I find this method a little nicer.

## Contribute
Welcome any suggestions/comments, even if only to trash-talk my rather ordinary design skills.
