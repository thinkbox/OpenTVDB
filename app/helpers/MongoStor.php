<?php

use FileStorage\Adapter\GridFS;
use FileStorage\Adapter\Local;
use FileStorage\FileStorage;

class MongoStor {


    private static function getAdapter()
    {

        //$connStr = "mongodb://";
        //$connStr .= (Config::get('database.mongodb.username') != '' && Config::get('database.mongodb.password') != '') ? Config::get('database.mongodb.username').':'.Config::get('database.mongodb.password').'@' : '';
        //$connStr .= Config::get('database.mongodb.host').':'.Config::get('database.mongodb.port');

        //$m = new MongoClient();
        //$gridfs = $m->selectDB(Config::get('database.mongodb.collection'))->getGridFS();
        //$adapter = new GridFS($gridfs);
        $adapter = new Local("/home/action/workspace/www/app/storage/images/");
        $storage = new FileStorage($adapter);

        return $storage;
    }

    public static function put($data)
    {
        $data = file_get_contents($data);
        $filename = md5($data);
        
        $storage = self::getAdapter();

        $storage->delete($filename);
        
        $file = $storage->init($filename);
        $file->setContent($data);
        $storage->save($file);
        
        return $filename;
    }

    public static function get($filename)
    {
        $file = self::getAdapter()->load($filename);
        return $file->getContent();
    }

    public static function getById($id)
    {
        $img = Images::find($id);
        if($img)
            return self::get($img->image_id);
    }

}

