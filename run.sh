#!/bin/sh
VERSION='1.0'
APP_ROOT="/path/to/wrush"
TMP_WPCONFIG="/tmp/wrush_wp_vars.php"
PWD=`pwd`
ROOT="$PWD/wp-config.php"

case "$1" in
	infos|blogs|crons|cron-posts|plugins)
		if [ ! -f $ROOT ];
		then
			command echo "Wrush v$VERSION : Developped by Nassim A. (http://nassi.me)"
			command echo "Wrush : You're not appear to be in Wordpress Path Installation"
			exit 0
		fi

		# Create Temp WP-Config.php
		command echo "<?php " > $TMP_WPCONFIG
		command grep -i -e "define(" -e "table_prefix" $ROOT >> $TMP_WPCONFIG
		command echo "?>" >> $TMP_WPCONFIG

		command /usr/bin/php $APP_ROOT/wrush.php $1 $APP_ROOT $TMP_WPCONFIG "$@"
		;;
		
	version)
		command echo "Wrush v$VERSION : Developped by Nassim A. (http://nassi.me)"
		command echo "This tool is like Drupal's Drush but for Wordpress"
		;;

	requirements)
		command echo "Wrush v$VERSION : Developped by Nassim A. (http://nassi.me)"
		command echo "This tool needs :"
		command echo "- PHP Client (v5.x or higher)"
		command echo "- PHP Curl libraries"
		command echo "- Wordpress installation"
		command echo ""
		command echo "Add 'wrush' to your bash profile like :"
		command echo "   alias wrush='$APP_ROOT/run.sh'"
		command echo ""
		command echo "Change run.sh variable APP_ROOT with your wrush root"
		command echo "/!\ Be careful on noexec on your system mount /!\\"
		command echo ""
		;;
	*)
		command echo "Wrush v$VERSION : Developped by Nassim A. (http://nassi.me)"
		command echo "This tool is like Drupal's Drush but for Wordpress"
		command echo "-----------------------------------------------------------"
		command echo "Usage : wrush (blogs|crons|plugins|infos|requirements|version) [OPTIONS] "
		command echo ""
		command echo "blogs - Show blogs list available in your instance"
		command echo "Options : none"
		command echo ""
		command echo "crons - Launch planified tasks"
		command echo "Options :"
		command echo "wrush crons [all|(http://)domain.tld(/blog)] [user:password]"
		command echo ""
		command echo "plugins - Get enabled plugins"
		command echo "Options : none"
		command echo ""
		command echo "infos - Get basic informations about your Wordpres installation"
		command echo "Options : none"
		command echo ""
		command echo "requirements - Show requirements for good utilisation of Wrush"
		command echo "Options : none"
		command echo ""
		command echo "version - Show Wrush version"
		command echo "Options : none"
		command echo ""
		exit 2
		;;
esac

exit 0
