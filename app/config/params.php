<?php
# app/config/params.php
if (getenv("OPENSHIFT_MYSQL_DB_HOST")!='') {

    $container->setParameter('database_host', getenv("OPENSHIFT_MYSQL_DB_HOST"));
    $container->setParameter('database_port', getenv("OPENSHIFT_MYSQL_DB_PORT"));
    $container->setParameter('database_name', "EmotionClock");
    $container->setParameter('database_user', getenv("OPENSHIFT_MYSQL_DB_USERNAME"));
    $container->setParameter('database_password', getenv("OPENSHIFT_MYSQL_DB_PASSWORD"));
    $container->setParameter('database.host', getEnv("OPENSHIFT_MYSQL_DB_HOST"));
    $container->setParameter('database.port', getEnv("OPENSHIFT_MYSQL_DB_PORT"));
}
?>
