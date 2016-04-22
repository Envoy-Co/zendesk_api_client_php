<?php

namespace Zendesk\API\Resources\HelpCenter;

use Zendesk\API\Exceptions\MissingParametersException;
use Zendesk\API\Traits\Resource\Defaults;
use Zendesk\API\Traits\Utility\InstantiatorTrait;

/**
 * Class Articles
 * https://developer.zendesk.com/rest_api/docs/help_center/articles
 */
class Articles extends ResourceAbstract
{
    use InstantiatorTrait;
    use Defaults {
        create as traitCreate;
        update as traitUpdate;
    }

    /**
     * {@inheritdoc}
     */
    protected $objectName = 'article';

    /**
     * @var locale
     */
    private $locale;

    /**
     * {@inheritdoc}
     */
    protected function setUpRoutes()
    {
        parent::setUpRoutes();

        $this->setRoutes([
            'create'                     => "help_center/{locale}/sections/{sectionId}/articles.json",
            'update'                     => "{$this->resourceName}/{articleId}/translations/{locale}.json",
            'updateSourceLocale'         => "{$this->resourceName}/{sectionId}/source_locale.json"
        ]);
    }

    /**
     * Create a new article within a section
     * Must be preceded by a call to Articles::setLocale()
     *
     * @param array $params
     *
     * @throws ResponseException
     * @throws \Exception
     * @return mixed
     */
    public function create(array $params = [])
    {
        $params = $this->addChainedParametersToParams($params, ['sectionId' => Sections::class]);

        if (! $this->getLocale()) {
            if ((isset($params['locale'])) && (! empty($params['locale']))) {
                $this->setLocale($params['locale']);
            } else {
                throw new MissingParametersException(__METHOD__, ['locale']);
            }
        }

        if (! $this->hasKeys($params, ['sectionId'])) {
            throw new MissingParametersException(__METHOD__, ['sectionId']);
        }

        return $this->traitCreate($params);
    }

    /**
     * Updates an article
     * Must be preceded by a call to Articles::setLocale()
     *
     * @param int $id
     * @param array $updateResourceFields
     *
     * @throws MissingParametersException
     * @throws ResponseException
     * @throws \Exception
     * @return mixed
     */
    public function update($id, array $updateResourceFields = [])
    {
        if (! $this->getLocale()) {
            if ((isset($updateResourceFields['locale'])) && (! empty($updateResourceFields['locale']))) {
                $this->setLocale($updateResourceFields['locale']);
            } else {
                throw new MissingParametersException(__METHOD__, ['locale']);
            }
        }

        $savedObjectName = $this->objectName;

        $this->objectName = 'translation';
        $update = $this->traitUpdate($id, $updateResourceFields);
        $this->objectName = $savedObjectName;

        return $update;
    }

    /**
     * @inheritdoc
     */
    public function getRoute($name, array $params = [])
    {
        if ($name === 'create') {
            return parent::getRoute($name, ['sectionId' => $params['sectionId'], 'locale' => $this->getLocale()]);
        } else
        if ($name === 'update') {
            return parent::getRoute($name, ['articleId' => $params['id'], 'locale' => $this->getLocale()]);
        }

        $routesWithLocale = ['findAll', 'find', 'update'];

        $locale = $this->getLocale();
        if (in_array($name, $routesWithLocale) && isset($locale)) {
            $originalResourceName = $this->resourceName;
            $this->resourceName   = "help_center/{$locale}/articles";

            $route = parent::getRoute($name, $params);

            // Reset resourceName so it doesn't affect succeeding calls
            $this->resourceName = $originalResourceName;

            return $route;
        } else {
            return parent::getRoute($name, $params);
        }
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return Categories
     */
    public function setLocale($locale)
    {
        if (is_string($locale)) {
            $this->locale = $locale;
        }

        return $this;
    }

    /**
     * Updates a articles source_locale property
     *
     * @param $articleId    The article to update
     * @param $sourceLocale The new source_locale
     *
     * @return array
     * @throws \Zendesk\API\Exceptions\RouteException
     */
    public function updateSourceLocale($articleId, $sourceLocale)
    {
        if (empty($articleId)) {
            $articleId = $this->getChainedParameter(get_class($this));
        }

        return $this->client->put(
            $this->getRoute(__FUNCTION__, ['articleId' => $articleId]),
            ['article_locale' => $sourceLocale]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getValidSubResources()
    {
        return [
            'article_attachments'  => ArticleAttachments::class,
            'article_labels'       => ArticleLabels::class
        ];
    }

}
