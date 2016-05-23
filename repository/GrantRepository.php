<?php

/**
 * @author mylampblog@163.com
 */

namespace pfdtk\oauth2\repository;

use pfdtk\oauth2\models\GrantsModel;
use pfdtk\oauth2\models\ScopesModel;
use pfdtk\oauth2\models\GrantScopesModel;
use yii\helpers\ArrayHelper;

class GrantRepository
{
    /**
     * @var string
     */
    const GRANT_AUTHORIZATIOIN_CODE = 'authorization_code';
    const GRANT_CREDENTIALS_CODE = 'client_credentials';
    const GRANT_IMPLICIT = 'implicit';
    const GRANT_PASSWORD = 'password';
    const GRANT_REFRESH_TOKEN = 'refresh_token';

    /**
     * @var array
     */
    static $grants = [
        self::GRANT_AUTHORIZATIOIN_CODE,
        self::GRANT_CREDENTIALS_CODE,
        self::GRANT_IMPLICIT,
        self::GRANT_PASSWORD,
        self::GRANT_REFRESH_TOKEN
    ];

    /**
     * initGrant
     */
    public function initGrant()
    {
        foreach (self::$grants as $item) {
            $model = new GrantsModel();
            $model->id = $item;
            $model->save();
        }
    }

    /**
     * case:
     *
     * $data = [
     *     'grantIdentifier1' => 'scopeIdentifier1',
     *     'grantIdentifier2' => 'scopeIdentifier2'
     * ]
     *
     * @param  array $data
     * @return boolean
     */
    public function bindGrantScope(array $data)
    {
        $grantIdentifiers = array_keys($data);
        $scopeIdentifiers = array_values($data);

        $grantsInDb = ArrayHelper::getColumn(GrantsModel::findByGrantId($grantIdentifiers)->all(), 'id');
        $scopesInDb = ArrayHelper::getColumn(ScopesModel::findByScopeId($scopeIdentifiers)->all(), 'id');

        if (count(array_diff($grantIdentifiers, $grantsInDb)) !== 0
            or count(array_diff($scopeIdentifiers, $scopesInDb)) !== 0
        ) {
            return false;
        }

        foreach ($data as $grant => $scope) {
            $clientGrantModel = new GrantScopesModel();
            $clientGrantModel->grant_id = $grant;
            $clientGrantModel->scope_id = $scope;
            $clientGrantModel->save();
        }

        return true;
    }

    /**
     * @param  string $grantIdentifier
     * @param  string $scope |null
     */
    public function removeGrantScope($grantIdentifier, $scope = null)
    {
        $condition = ['grant_id' => $grantIdentifier];
        if ($scope) {
            $condition['scope_id'] = $scope;
        }
        $scopes = GrantScopesModel::findAll($condition);

        foreach ($scopes as $scope) {
            $scope->delete();
        }
    }

}