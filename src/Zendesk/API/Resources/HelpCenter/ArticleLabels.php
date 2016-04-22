<?php

namespace Zendesk\API\Resources\HelpCenter;

use Zendesk\API\Exceptions\MissingParametersException;
use Zendesk\API\Traits\Resource\Defaults;
use Zendesk\API\Traits\Utility\InstantiatorTrait;

/**
 * Class ArticleLabels
 * https://developer.zendesk.com/rest_api/docs/help_center/labels
 */
class ArticleLabels extends ResourceAbstract
{
    use InstantiatorTrait;
    use Defaults {
        create as traitCreate;
    }

    /**
     * {@inheritdoc}
     */
    protected $objectName = 'label';

    /**
     * {@inheritdoc}
     */
    protected function setUpRoutes()
    {
        $this->setRoutes([
            'create' => "help_center/articles/{articleId}/labels.json"
        ]);
    }

    /**
     * Create a new label within an article
     *
     * @param array $params
     *
     * @throws ResponseException
     * @throws \Exception
     * @return mixed
     */
    public function create(array $params = [])
    {
        $params = $this->addChainedParametersToParams($params, ['articleId' => Articles::class]);

        if (! $this->hasKeys($params, ['name', 'articleId'])) {
            throw new MissingParametersException(__METHOD__, ['name', 'articleId']);
        }

        return $this->traitCreate($params);
    }

}
