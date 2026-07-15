<?php
class CFirebase 
{

    public static function addDocument($key='',$project_id='',$id='',$params=array())
    {
        $data = ["fields" => (object)$params];
        $json = json_encode($data);

        $url = "https://firestore.googleapis.com/v1beta1/projects/$project_id/databases/(default)/documents/drivers/".$id;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array('Content-Type: application/json',
                'Content-Length: ' . strlen($json),
                'X-HTTP-Method-Override: PATCH'),
            CURLOPT_URL => $url . '?key='.$key,
            CURLOPT_USERAGENT => 'cURL',
            CURLOPT_POSTFIELDS => $json
        ));

        $response = curl_exec( $curl );
        curl_close( $curl );
        return $response;
    }

}