<?php

namespace Freshdesk;

use Freshdesk\Model\Contact as ContactM;
class Contact extends Rest
{

    /**
     * @param $id
     * @param ContactM $model
     * @return $this
     * @throws \RuntimeException
     */
    public function getContactById($id, ContactM $model = null)
    {
        $response = json_decode(
            $this->restCall(
                '/contacts/'.$id.'.json',
                Rest::METHOD_GET
            )
        );
        if (property_exists($response, 'errors'))
            throw new \RuntimeException(
                sprintf('Error: %s', $response->errors->error)
            );
        if ($model === null)
            $model = new ContactM();
        return $model->setAll(
            $response
        );
    }
}