<?php
define('MAX_SIZE',(1024*1024*10)); // 10mb

class UploaderController extends Controller {


    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly', // we only allow deletion via POST request
        );
    }


    public function accessRules()
    {
        return array(
            array('allow',
                  'actions'=>array('upload','setFav', 'delete'),
                  'roles'=>array('guest'),
            ),
            array('allow',
                  'roles'=>array('user'),
            ),
            array('deny',  // deny all users
                  'users'=>array('*'),
            ),
        );
    }


    function getKeys()
    {
        switch($_POST['upload_type'])
        {
            case 'product':
                if(Yii::app()->user->isGuest)
                {
                    $r['file_key'] = 'ProductImg';
                    $r['params_key'] = Product::IMG_PATH_GUEST;
                    $r['key_id_item'] = 'id_product';
                }
                else
                {
                    $r['file_key'] = 'ProductImg';
                    $r['params_key'] = Product::IMG_PATH;
                    $r['key_id_item'] = 'id_product';
                }
                break;
            case 'user_logo':
                $r['file_key'] = 'UserLogo';
                $r['params_key'] = 'user_logo_tmp';
                break;
        }

        if( $r )
        {
            return $r;
        }
        else{
            return false;
        }
    }


    function actionUpload()
    {
        $keys = $this->getKeys();
        if(!$keys)
        {
            return false;
        }


        $key = $keys['file_key'];
        $t = time();
        $img_dir = Yii::app()->params[$keys['params_key']];
        $date = date($img_dir['date_fmt'],$t);
        if(Yii::app()->user->isGuest)
        {
            $id_user = Yii::app()->session->sessionID;
        }
        else
        {
            $id_user = Yii::app()->user->id;
        }

        if( !Yii::app()->user->isGuest )
        {
            if(empty($_POST['id_item']) && empty(Yii::app()->session[$keys['key_id_item']]))
            {
                $item = new Product();
                $item->attributes = $_POST['Product'];
                $item->item_state = Product::STATE_ROUGH_COPY&Product::STATE_PUBLISH;
                $item->id_cat = Category::checkCategory($_POST['Product']['category']);
                $item->save(false);
                $id_item = $item->id;
                Yii::app()->session[$keys['key_id_item']] = $id_item;

                if(!empty($_POST['ProductInfo']['full_text'])){
                    $item_i = new ProductInfo();
                    $item_i->id = $id_item;
                    $item_i->attributes = $_POST['ProductInfo'];
                    $item_i->save(false);
                }
            }
            else if(!empty(Yii::app()->session[$keys['key_id_item']]))
            {
                $id_item = Yii::app()->session[$keys['key_id_item']];
                //$item = Product::model()->findByPk(Yii::app()->session[$keys['key_id_item']]);
            }
            else if(!empty($_POST['id_item']))
            {
                //$item = Product::model()->findByPk($_POST['id_item']);
                $id_item = $_POST['id_item'];
            }

            $mainImg = Yii::app()->db->createCommand('SELECT prod_images.id, prod_images.date_create, prod_images.img_url FROM {{product}} product INNER JOIN {{prod_images}} prod_images ON product.id_mainImg=prod_images.id WHERE product.id=:id')->bindParam(':id',$id_item)->queryRow();
            $c_images = Yii::app()->db->createCommand('SELECT COUNT(id) FROM {{prod_images}} WHERE id_prod=:prod')->bindParam(':prod',$id_item)->queryScalar();
        }
        else
        {
            $mainImg = Yii::app()->db->createCommand('SELECT id, date_create, img_url FROM {{prod_temp_images}} images WHERE id_session=:id AND is_main=1')->bindParam(':id',$id_user)->queryRow();
            $c_images = Yii::app()->db->createCommand('SELECT COUNT(id) FROM {{prod_temp_images}} WHERE id_session=:prod')->bindParam(':prod',$id_user)->queryScalar();
        }

        foreach($img_dir as $k=>&$dir)
        {
            if( $k=='date_fmt' ) continue;

            if( is_array($dir) )
            {
                foreach($dir as &$path)
                {
                    $path = strtr($path,array('{id_user}'=>$id_user, '{id_item}'=>$id_item));
                    CLib::createFolders($path);
                }
            }
            else
            {
                $dir = strtr($dir,array('{id_user}'=>$id_user, '{id_item}'=>$id_item));
                CLib::createFolders($dir);
            }
        }

        $targetFolder     = $img_dir['origin'];
        $bigFolder        = $img_dir['big'];
        $tmpFolder        = $img_dir['tmp'];
        $previewFolder    = $img_dir['preview'];

        if(!count($_FILES[$key]))
        {
            return false;
        }

        $info = array();
        $size = 1200; // px

        $max_files = Yii::app()->params['max_images'];

        //foreach($_FILES[$key]['tmp_name'] as $i=>$file)
        {

            $tempFile = $_FILES[$key]['tmp_name'];

            $fileName = md5($id_user.$_FILES[$key]['name'].$t.rand(0,1000));

            $type = explode('/',$_FILES[$key]['type']);
            //$fileName.='.'.$type[1];
            $fileName.='.jpeg';

            $newFile = $tmpFolder . $fileName; // перенести во временную папку

            move_uploaded_file($tempFile,$newFile);
            if( !file_exists($newFile) )
            {
                $vr['err'] = CLib::param('FILE_TO_BIG');
                $vr['no_image'] = true;
                echo json_encode(array('files'=>array($vr)));
                return;
            }

            $f_size = filesize($newFile);

            // TODO

            if( $max_files <= $c_images )
            {
                unlink($newFile);
                $vr['err'] = CLib::param('FILE_IMAGE_LIMIT');
                $vr['err_limit'] = true;
            }
            else if( $f_size>MAX_SIZE )
            {
                unlink($newFile);
                $vr['err'] = CLib::param('FILE_TO_BIG');
                $vr['no_image'] = true;
            }
            else{

                $mime = explode('/', CFileHelper::getMimeType($newFile));

                if( $mime[1]!='jpeg'&&$mime[1]!='jpg')
                {
                    unlink($newFile);
                    $vr['err'] = CLib::param('FILE_NO_IMAGE');
                    $vr['no_image'] = true;
                }
                else
                {
                    $w_norm = $h_norm = $size;
                    copy($newFile, $targetFolder.$fileName);
                    CLib::resizeImgToSmall($w_norm, $h_norm, $targetFolder.$fileName, false, 80, false, true); //
                    $size = 1200;
                    $w_norm = $h_norm = $size;
                    $t_start = microtime(true);
                    unlink($newFile);
                    $newFile = $targetFolder.$fileName;
                    copy($newFile, $bigFolder.$fileName);
                    CLib::resizeImgToSmall($w_norm, $h_norm, $bigFolder.$fileName, false, 80); //
                    $t_end = microtime(true);
                    $vr['time']['diff2'][] = $t_end-$t_start;

                    foreach($previewFolder as $size=>$folder)
                    {
                        $w_norm = $h_norm = (int)$size;
                        if( !file_exists($folder) )
                        {
                            mkdir($folder);
                        }

                        $img = $folder.$fileName;
                        copy($newFile, $img);

                        CLib::resizeImgToSmall($w_norm, $h_norm, $img, $size==300 || $size==100, 80);
                    }
                    //unlink($newFile);
                    //$command->bindParam(':filename',$fileName)->execute();
                    //$id = Yii::app()->db->getLastInsertID();
                    if( !Yii::app()->user->isGuest )
                    {
                        $sql = 'INSERT INTO {{prod_images}} SET id_prod=:prod, img_url=:url, date_create=:date';
                        Yii::app()->db->createCommand($sql)->bindParam(':prod',$id_item)->bindParam(':url',$fileName)->bindParam(':date',$t)->execute();
                    }
                    else
                    {
                        $sql = 'INSERT INTO {{prod_temp_images}} SET id_session=:id_user, img_url=:url, date_create=:date';
                        Yii::app()->db->createCommand($sql)->bindParam(':id_user',$id_user)->bindParam(':url',$fileName)->bindParam(':date',$t)->execute();

                    }
                    /*
                    $img_model = new ProdImages();
                    $img_model->id_prod = $id_item;
                    $img_model->img_url = $fileName;
                    $img_model->date_create = $t;
                    $img_model->save(false);
                    */
                    $id_img = Yii::app()->db->lastInsertID;
                    if(!Yii::app()->user->isGuest)
                    {
                        if( empty($mainImg['date_create']) || !CLib::checkProdImg(Yii::app()->user->id, $id_item, $mainImg['img_url'])  )
                        {
                            $mainImg['id'] = $id_img;
                            Yii::app()->db->createCommand('UPDATE {{product}} SET id_mainImg=:img WHERE id=:id')->bindParam(':id',$id_item)->bindParam(':img',$id_img)->execute();
                            /*
                            $item->id_mainImg = $img_model->id;
                            $item->save(false);
                            */
                        }
                    }
                    else
                    {
                        if( !isset(Yii::app()->session['prod_images']) )
                        {
                            $arr = array();
                        }
                        else
                        {
                            $arr = Yii::app()->session['prod_images'];
                        }
                        $arr[] = $id_img;
                        Yii::app()->session['prod_images'] = $arr;

                        if( empty($mainImg['date_create']) || !CLib::checkProdImg($id_user, $id_item, $mainImg['img_url'])  )
                        {
                            $mainImg['id'] = $id_img;
                            Yii::app()->db->createCommand('UPDATE {{prod_temp_images}} SET is_main=1 WHERE id=:id')->bindParam(':id',$id_img)->execute();
                        }
                    }

                    $vr['thumbUrl'] = SITE_URL.$previewFolder['100'].$fileName;
                    $vr['thumbUrl_100'] = SITE_URL.$previewFolder['100'].$fileName;
                    $vr['thumbUrl_300'] = SITE_URL.$previewFolder['300'].$fileName;
                    $vr['id']  = $id_img;
                    $vr['id_main']  = $mainImg['id'];


                    $vr['time']['start'] = $t_start;
                    $vr['time']['end'] = $t_end;
                    $vr['time']['diff'] = $t_end-$t_start;
                }
            }
            $info[] = $vr;
        }

        echo json_encode(array('files'=>$info));
    }



    function actionDelete()
    {
        $id = $_POST['id'];
        $type_img = $_POST['type_img']; // пока `product`

        if( !Yii::app()->user->isGuest )
        {
            $p['condition'] = ' product.id_user=:id_user';
            $p['params'][':id_user'] = Yii::app()->user->id;

            $image = ProdImages::model()->with('product')->findByPk($id,$p);
            if(!$image) return false;

            $product = $image->product;

            $img_path_key = Product::IMG_PATH;

            $this->deleteFile(Yii::app()->user->id, $img_path_key, $image->id_prod, $image->img_url);
            if( $product->id_mainImg == $image->id)
            {
                $id_mainImg = 0;
                $images = $product->images;
                foreach($images as $img)
                {
                    if( $product->id_mainImg == $img->id )
                    {
                        continue;
                    }
                    if( CLib::checkProdImg($product->id_user, $product->id, $img->img_url) )
                    {
                        $id_mainImg = $img->id;
                        break;
                    }
                }

                $product->id_mainImg = $id_mainImg;
                $product->save(false);
            }

            $image->delete();
            echo json_encode(array('ok'=>'ok', 'id_main'=>$product->id_mainImg));
        }
        else
        {
            $sql = 'SELECT id, img_url, date_create, is_main FROM {{prod_temp_images}} WHERE id=:id AND id_session=:id_user';
            $image = Yii::app()->db->createCommand($sql)->bindParam(':id', $id)->bindValue(':id_user', Yii::app()->session->sessionID)->queryRow();
            if(!$image)
            {
                return false;
            }

            $img_path_key = Product::IMG_PATH_GUEST;
            $this->deleteFile(Yii::app()->session->sessionID, $img_path_key, $image['id_prod'], $image['img_url']);
            Yii::app()->db->createCommand('DELETE FROM {{prod_temp_images}}  WHERE id=:id')->bindParam(':id', $id)->execute();
            if( $image['is_main']==1 )
            {
                $sql = 'UPDATE {{prod_temp_images}} SET is_main=1 WHERE id_session=:id_user LIMIT 1';
                Yii::app()->db->createCommand($sql)->bindValue(':id_user', Yii::app()->session->sessionID)->execute();
                $sql = 'SELECT id FROM {{prod_temp_images}} WHERE id_session=:id_user AND is_main=1';
                $main_image = Yii::app()->db->createCommand($sql)->bindValue(':id_user', Yii::app()->session->sessionID)->queryRow();
            }

            echo json_encode(array('ok'=>'ok', 'id_main'=>$main_image['id']));
        }
    }

    function actionSetFav()
    {
        $id = $_POST['id'];
        $type_img = $_POST['type_img']; // пока `product`

        if( !Yii::app()->user->isGuest )
        {
            $p['condition'] = ' product.id_user=:id_user';
            $p['params'][':id_user'] = Yii::app()->user->id;

            $image = ProdImages::model()->with('product')->findByPk($id,$p);
            if(!$image) return false;

            $product = $image->product;
            $product->id_mainImg = $id;
            $product->save(false);
        }
        else
        {
            $sql = 'UPDATE {{prod_temp_images}} SET is_main = (CASE WHEN id!=:id  THEN 0 ELSE 1 END) WHERE id_session=:id_user';
            Yii::app()->db->createCommand($sql)->bindParam(':id',$id)->bindValue(':id_user', Yii::app()->session->sessionID)->execute();
        }

        echo json_encode(array('ok'=>'ok'));
    }

    function deleteFile($id_user, $img_path_key, $id_item, $img_url)
    {
        $img_dir = Yii::app()->params[$img_path_key];

        foreach($img_dir as $k=>&$dir)
        {
            if( $k=='date_fmt' ) continue;

            if( is_array($dir) )
            {
                foreach($dir as &$path)
                {
                    $path = strtr($path,array('{id_user}'=>$id_user, '{id_item}'=>$id_item));
                    if( is_file($path.$img_url) && file_exists($path.$img_url) )
                    {
                        unlink($path.$img_url);
                    }
                }
            }
            else
            {
                $dir = strtr($dir,array('{id_user}'=>$id_user, '{id_item}'=>$id_item));
                if( is_file($dir.$img_url) && file_exists($dir.$img_url) )
                {
                    unlink($dir . $img_url);
                }
            }

        }
    }


    function createFolders(&$img_dir, $t, $create = true)
    {
        $date = date($img_dir['date_fmt'],$t);
        foreach($img_dir as $k=>&$dir)
        {
            if( $k=='date_fmt' ) continue;

            if( is_array($dir) )
            {
                foreach($dir as &$path)
                {
                    $path = strtr($path,array('{id_user}'=>Yii::app()->user->id, '{date}'=>$date));
                    if( $create )
                    {
                        //mkdir($path,0755,true);
                        CLib::createFolders($path);
                    }
                }
            }
            else
            {
                $dir = strtr($dir,array('{id_user}'=>Yii::app()->user->id, '{date}'=>$date));
                if( $create )
                {
                    //mkdir($path,0755,true);
                    CLib::createFolders($dir);
                }
            }

        }
    }


    function actionUploadUserLogo()
    {
        $keys = $this->getKeys();
        if(!$keys)
        {
            return false;
        }


        $key = $keys['file_key'];
        $t = time();
        $img_dir = Yii::app()->params[$keys['params_key']];

        $this->createFolders($img_dir, $t);

        $tmpFolder        = $img_dir['tmp'];


        if(!count($_FILES[$key]))
        {
            return false;
        }

        $info = array();
        $size = 2000; // px


        {
            $tempFile = $_FILES[$key]['tmp_name'];

            $fileName = md5(Yii::app()->user->id.$_FILES[$key]['name'].$t.rand(0,1000));

            $fileName.='.jpeg';

            $newFile = $tmpFolder . $fileName; // перенести во временную папку

            move_uploaded_file($tempFile,$newFile);
            $f_size = filesize($newFile);

            if( $f_size>MAX_SIZE )
            {
                unlink($newFile);
                $vr['err'] = CLib::param('FILE_TO_BIG');
                $vr['no_image'] = true;
            }
            else{

                $mime = explode('/', CFileHelper::getMimeType($newFile));

                if( $mime[1]!='jpeg'&&$mime[1]!='jpg')
                {
                    unlink($newFile);
                    $vr['err'] = CLib::param('FILE_NO_IMAGE');
                    $vr['no_image'] = true;
                }
                else
                {
                    $w_norm = $h_norm = $size;
                    copy($newFile, $tmpFolder.$fileName);
                    CLib::resizeImgToSmall($w_norm, $h_norm, $tmpFolder.$fileName, false, 100); //

                    $sql = 'INSERT INTO {{user_logo_tmp}} SET id_user=:user, date_create=:date, img_url=:img_url';
                    Yii::app()->db->createCommand($sql)->bindParam(':user', Yii::app()->user->id)->bindParam(':date', $t)->bindParam(':img_url',$fileName)->execute();

                    $info['url'] = SITE_URL.$tmpFolder.$fileName;
                    $info['id']  = Yii::app()->db->lastInsertId;
                }
            }

        }

        echo json_encode(array('files'=>$info));
    }

    function actionChangeUserLogo()
    {
        $offset = $_POST['offset'];
        $zoom = $_POST['zoom'];

        $sql = 'SELECT id, date_create, img_url, id_user FROM {{user_logo_tmp}} WHERE id_user=:user ORDER BY id DESC';
        $image = Yii::app()->db->createCommand($sql)->bindParam(':user', Yii::app()->user->id)->queryRow();

        if(!$image)
        {
            $image['date_create'] = Yii::app()->user->model->time_reg;
            $image['img_url'] = Yii::app()->user->model->img_url;
            $img_dir = Yii::app()->params[User::IMG_PATH];
            $this->createFolders($img_dir, $image['date_create'], false);
            $tmpFolder        = $img_dir['origin'];
        }
        else
        {
            $img_dir = Yii::app()->params[User::IMG_TMP_PATH];
            $this->createFolders($img_dir, $image['date_create'], false);
            $tmpFolder        = $img_dir['tmp'];
        }

        $filenameOld = $image['img_url'];
        $filenameNew = md5((Yii::app()->user->id).(time())).'.jpeg';

        $fileName = $filenameOld;

        $img_dir_upload = Yii::app()->params[User::IMG_PATH];

        $this->createFolders($img_dir_upload, Yii::app()->user->model->time_reg);

        $size = 200; // px
        $newFile = $tmpFolder.$fileName;

        {
            $f_size = filesize($newFile);

            if( $f_size>MAX_SIZE )
            {
                unlink($newFile);
                $info['err'] = CLib::param('FILE_TO_BIG');
                $info['no_image'] = true;
            }
            else{

                $mime = explode('/', CFileHelper::getMimeType($newFile));

                if( $mime[1]!='jpeg'&&$mime[1]!='jpg')
                {
                    unlink($newFile);
                    $info['err'] = CLib::param('FILE_NO_IMAGE');
                    $info['no_image'] = true;
                }
                else
                {
                    copy($newFile, $img_dir_upload['origin'].$filenameNew);
                    $newFile = $img_dir_upload['origin'].$filenameNew;

                    $x = abs($offset['x'])/$zoom;
                    $y = abs($offset['y'])/$zoom;
                    $width  = $size/($zoom);
                    $height = $size/($zoom);

                    if( ($x!=0 && empty($x)) || ($y!=0 && empty($y)) )// || (empty($width) && $width<200 ) || (empty($height) && $height<200 ) )
                    {
                        echo json_encode(array('err'=>CLib::param('SOMETHING_WRONG'), 'data'=>array($x,$y)));
                        return;
                    }
                    copy( $newFile, $img_dir_upload['big'].$filenameNew );
                    CLib::crop($img_dir_upload['big'].$filenameNew, $x, $y, $width, $height, 100);

                    foreach( $img_dir_upload['preview'] as $size=>$path )
                    {
                        copy( $img_dir_upload['big'].$filenameNew, $path.$filenameNew );
                        $w_norm = $h_norm = $size;
                        CLib::resizeImgToSmall($w_norm, $h_norm, $path.$filenameNew, false, 100); //
                    }

                    unlink($tmpFolder.$fileName);
                    $sql = 'DELETE FROM {{user_logo_tmp}} WHERE id_user=:user';
                    Yii::app()->db->createCommand($sql)->bindParam(':user', Yii::app()->user->id)->execute();

                    $this->deleteFile(Yii::app()->user->id, User::IMG_PATH, Yii::app()->user->model->time_reg, Yii::app()->user->model->img_url );

                    Yii::app()->user->model->img_url = $filenameNew;
                    $set = Yii::app()->user->siteSet;
                    Yii::app()->user->model->save(false);

                    $set[User::SET_LOGO] = array('offset'=>$offset,'zoom'=>$zoom);
                    Yii::app()->user->siteSet = $set;

                    $info['ok'] = true;
                    $info['url'] = SITE_URL.$img_dir_upload['preview']['200'].$filenameNew;

                }
            }

        }

        echo json_encode($info);
    }


} 