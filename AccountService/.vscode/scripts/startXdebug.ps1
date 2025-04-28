# If this setting is set to 1 then errors will always be displayed, no matter what the setting of PHP's display_errors is.
# To apply: Uncomment the line below and restart the service:
#docker compose exec php sh -c 'sed -i "/xdebug.force_display_errors=0/c\\xdebug.force_display_errors=1" $PHP_INI_DIR/conf.d/xdebug.ini'
docker compose exec php sh -c 'sed -i "/xdebug.dump_globals=false/c\\xdebug.dump_globals=true" $PHP_INI_DIR/conf.d/xdebug.ini'
docker compose exec php sh -c 'sed -i "/xdebug.show_local_vars=0/c\\xdebug.show_local_vars=3" $PHP_INI_DIR/conf.d/xdebug.ini'
docker compose exec php sh -c 'sed -i "/xdebug.profiler_enable=0/c\\xdebug.profiler_enable=1" $PHP_INI_DIR/conf.d/xdebug.ini'
docker compose exec php sh -c 'sed -i "/xdebug.dump.SERVER=*/c\\xdebug.dump.SERVER=0" $PHP_INI_DIR/conf.d/xdebug.ini'
docker compose exec php sh -c 'apachectl graceful'