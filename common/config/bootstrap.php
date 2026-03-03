<?php

$root = dirname(dirname(__DIR__));
if (file_exists($root . '/.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable($root);
    $dotenv->safeLoad();
}

Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@api', dirname(dirname(__DIR__)) . '/api');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');
