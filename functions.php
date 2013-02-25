<?php

function init_wp_variables($config = "wp-config.php") {
        if (file_exists($config)) {
                require_once($config);
        	$values['state'] = 1;
        	@$values['bdd_host'] = DB_HOST;
        	@$values['bdd_user'] = DB_USER;
        	@$values['bdd_pass'] = DB_PASSWORD;
        	@$values['bdd_name'] = DB_NAME;
                @$values['bdd_prefix'] = $table_prefix;
        	@$values['wp_lang'] = WPLANG;
        	@$values['wp_debug'] = WP_DEBUG;
        	@$values['wp_multisite'] = WP_ALLOW_MULTISITE;
        } else {
                $values['state'] = 0;
        }

	return($values);
}

function rm_backslash_n($pattern = "\n") {
        $result = str_replace("\n", "", $pattern);
        return $result;
}

// Display informations returned by "infos" option.
function wrush_infos($bdd, $prefix = "wp_") {
        $resultat = $bdd->query("SELECT option_name, option_value FROM ".$prefix."options WHERE option_name IN ('siteurl','blogname','admin_email')");
        $resultat->setFetchMode(PDO::FETCH_OBJ);

        echo "Wordpress main informations\n";
        echo "---------------------------\n";

        while( $ligne = $resultat->fetch() )
        {
                echo "- ".$ligne->option_name." : ".$ligne->option_value."\n";
        }

        echo " --- \n";
        # Test versions
        $version_file = rm_backslash_n(shell_exec('pwd')."/wp-includes/version.php");
 
        if(file_exists($version_file)) {
                require_once($version_file);

                # PHP
                $system_php = rm_backslash_n(shell_exec("php -v | grep -i cli | awk {'print $2'}"));
                echo "- PHP Version installed : $system_php (Required : $required_php_version)\n";
                echo "- Wordpress version : $wp_version\n";
        }
}

// Display informations returned by "blogs" option.
function wrush_blogs($bdd, $prefix = "wp_", $multisite = "false") {
        echo "Wordpress blogs list\n";
        echo "---------------------------\n";

        if($multisite == "true")
        {
                $resultat = $bdd->query("SELECT domain, path, deleted, public FROM ".$prefix."blogs");
                $resultat->setFetchMode(PDO::FETCH_OBJ);

                while( $ligne = $resultat->fetch() )
                {
                        echo "- http://".$ligne->domain.$ligne->path." : ";
                        echo "Public=".$ligne->public.", Deleted=".$ligne->deleted;
                        echo "\n";
                }
        } else {
                $resultat = $bdd->query("SELECT option_value FROM ".$prefix."options WHERE option_name = 'siteurl'");
                $resultat->setFetchMode(PDO::FETCH_OBJ);

                while( $ligne = $resultat->fetch() )
                {
                        echo "- ".$ligne->option_value;
                        echo "\n";
                }
        }
}

// Display informations returned by "cron-posts" option.
function wrush_cron_posts($bdd, $prefix = "wp_", $multisite = "false") {
        echo "Wordpress scheduled posts\n";
        echo "---------------------------\n";

        if($multisite == "true")
        {
                $resultat = $bdd->query("SELECT blog_id, domain, path FROM ".$prefix."blogs");
                $resultat->setFetchMode(PDO::FETCH_OBJ);

                while( $ligne = $resultat->fetch() )
                {
                        echo "- http://".$ligne->domain.$ligne->path." : ";
                        echo "\n";

                        $resultat_sub = $bdd->query("SELECT post_title, post_date FROM ".$prefix.$ligne->blog_id"_posts WHERE post_status = 'future'");
                        $resultat_sub->setFetchMode(PDO::FETCH_OBJ);

                        while( $ligne_sub = $resultat_sub->fetch() )
                        {
                                echo "--> ".$ligne_sub->post_date." : ".$ligne_sub->post_title.".\n";
                        }
                }
        } else {
                $resultat = $bdd->query("SELECT option_value FROM ".$prefix."options WHERE option_name = 'siteurl'");
                $resultat->setFetchMode(PDO::FETCH_OBJ);

                while( $ligne = $resultat->fetch() )
                {
                        echo "- ".$ligne->option_value;
                        echo "\n";
                }
        }
}

// Display informations returned by "plugins" option.
function wrush_plugins($bdd, $prefix = "wp_", $multisite = "false") {
        if($multisite == "false") {
                $resultat = $bdd->query("SELECT blog_id, domain, path FROM ".$prefix."blogs WHERE deleted = 0 AND public = 1");
                echo "Wordpress plugins enabled on each blogs\n";

                $resultat->setFetchMode(PDO::FETCH_OBJ);
                echo "---------------------------\n";

                while( $ligne = $resultat->fetch() )
                {
                        echo "- http://".$ligne->domain.$ligne->path." : ";
                        echo "\n";
                        if($ligne->path == "/") {
                                $resultat_sub = $bdd->query("SELECT option_value FROM ".$prefix."options WHERE option_name = 'active_plugins'");
                        } else {
                                $resultat_sub = $bdd->query("SELECT option_value FROM ".$prefix.$ligne->blog_id."_options WHERE option_name = 'active_plugins'");
                        }
                        $resultat_sub->setFetchMode(PDO::FETCH_OBJ);
                        while( $ligne_sub = $resultat_sub->fetch() )
                        {
                                preg_match_all('/s:[0-9]{2}:"(.*?)"\;/',$ligne_sub->option_value,$match);
                                foreach($match[1] as $plugin)
                                {
                                        $exploded = explode("/", $plugin);
                                        echo "___ ".$exploded[0]."\n";
                                }
                        }
                        echo "\n";

                }
        } else {
                $resultat = $bdd->query("SELECT option_value FROM ".$prefix."options WHERE option_name = 'siteurl'");
                echo "Wordpress plugins enabled on your blog\n";

                $resultat->setFetchMode(PDO::FETCH_OBJ);
                echo "---------------------------\n";

                while( $ligne = $resultat->fetch() )
                {
                        echo "- ".$ligne->option_value." : ";
                        echo "\n";
                        $resultat_sub = $bdd->query("SELECT option_value FROM ".$prefix."options WHERE option_name = 'active_plugins'");
                        $resultat_sub->setFetchMode(PDO::FETCH_OBJ);
                        while( $ligne_sub = $resultat_sub->fetch() )
                        {
                                preg_match_all('/s:[0-9]{2}:"(.*?)"\;/',$ligne_sub->option_value,$match);
                                foreach($match[1] as $plugin)
                                {
                                        $exploded = explode("/", $plugin);
                                        echo "___ ".$exploded[0]."\n";
                                }
                        }
                        echo "\n";

                }
        }
}

// Display informations returned by "blogs" option.
function wrush_crons($bdd, $prefix = "wp_", $multisite = "false", $action = "all", $auth = 0) {
        echo "Wordpress blogs WP-Cron launcher\n";
        echo "---------------------------\n";

        if($multisite == "true")
        {
                if ($action != "all") {
                        $action = str_replace("http://", "", $action);
                        $exploded_url = explode("/", $action);
                        if(!isset($exploded_url[1]) || $exploded_url[1] == "")
                        {
                                $resultat = $bdd->query("SELECT domain, path FROM ".$prefix."blogs WHERE deleted = 0 AND public = 1 AND domain LIKE \"%".$exploded_url[0]."%\" AND path = \"/\"");
                        } else {
                                $resultat = $bdd->query("SELECT domain, path FROM ".$prefix."blogs WHERE deleted = 0 AND public = 1 AND domain LIKE \"%".$exploded_url[0]."%\" AND path LIKE \"%".$exploded_url[1]."%\"");
                        }
                } else {
                        $resultat = $bdd->query("SELECT domain, path FROM ".$prefix."blogs WHERE deleted = 0 AND public = 1");
                }
                $resultat->setFetchMode(PDO::FETCH_OBJ);
                
                if($resultat->rowCount())
                {
                        while( $ligne = $resultat->fetch() )
                        {
                                echo "- http://".$ligne->domain.$ligne->path." : ";

                                $result = curl_request($ligne->domain, $ligne->path, $auth);

                                if ($result['http_code'] == '200') { 
                                        echo "OK \n"; 
                                } else { 
                                        echo "Error (".$result['http_code'].")\n"; 
                                }

                                #if ($result['curl_error'])    throw new Exception($result['curl_error']);
                                #if ($result['http_code']!='200')    throw new Exception("HTTP Code = ".$result['http_code']);
                                #if (!$result['body'])        throw new Exception("Body of file is empty");

                        }
                } else {
                        echo "Wrush : Sorry, requested blog doesn't exist.";
                }

        } else {
                $resultat = $bdd->query("SELECT option_name, option_value FROM ".$prefix."options WHERE option_name = 'siteurl'");
                $resultat->setFetchMode(PDO::FETCH_OBJ);

                while( $ligne = $resultat->fetch() )
                {
                        echo "- ".$ligne->option_value." : ";

                        $cut = str_replace("http://", "", $ligne->option_value);
                        $explode_url = explode("/", $cut);

                        if(!isset($exploded_url[1]) || $exploded_url[1] == "")
                        {
                                $path_blog = "";
                        } else {
                                $path_blog = $explode_url[1];
                        }
                        $result = curl_request($explode_url[0], $path_blog, $auth);

                        if ($result['http_code'] == '200') { 
                                echo "OK \n"; 
                        } else { 
                                echo "Error (".$result['http_code'].")\n"; 
                        }

                        #if ($result['curl_error'])    throw new Exception($result['curl_error']);
                        #if ($result['http_code']!='200')    throw new Exception("HTTP Code = ".$result['http_code']);
                        #if (!$result['body'])        throw new Exception("Body of file is empty");

                }
        }
}

function curl_request ($host, $path, $auth) {
        $host_header= gethostname();
        $ip = gethostbyname($host_header);

        $params = array('url' => 'http://'.$ip.'/'.$path.'/wp-cron.php?doing_wp_cron',
                'host' => $host,
                'header' => '',
                'method' => 'GET',
                'referer' => '',
                'cookie' => '',
                'post_fields' => '',
                'timeout' => 30
        );
        if ($auth) {
                $explode_auth = explode(":", $auth);
                if(isset($explode_auth[0]) && isset($explode_auth[1]))
                {
                        $params['login'] = $explode_auth[0];
                        $params['password'] = $explode_auth[1];
                }
        }

        $curl = new CurlRequest;
        $curl->init($params);
        $result = $curl->exec();

        return $result;
}

?>
