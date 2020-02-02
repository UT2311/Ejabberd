Ejabberd Wrapper

How to use

Install this wrapper by below command -
composer require Utkarsh/Ejabberd
$ejabberd = Config::get('ejabberd'); 
$ejabberd->createRoom($room_name);