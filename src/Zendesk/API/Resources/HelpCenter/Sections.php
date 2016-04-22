<?php

namespace Zendesk\API\Resources\HelpCenter;

use Zendesk\API\Traits\Resource\Defaults;
use Zendesk\API\Traits\Utility\InstantiatorTrait;

/**
 * Class Sections
 * https://developer.zendesk.com/rest_api/docs/help_center/sections
 */
class Sections extends ResourceAbstract
{
    use InstantiatorTrait;
    use Defaults {
        create as traitCreate;
    }

    /**
     * {@inheritdoc}
     */
    protected $objectName = 'section';

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
            'create'                     => "help_center/categories/{categoryId}/sections.json",
            'updateSourceLocale'         => "{$this->resourceName}/{categoryId}/source_locale.json"
        ]);
    }

    /**
     * Create a new section within a category
     *
     * @param array $params
     *
     * @throws ResponseException
     * @throws \Exception
     * @return mixed
     */
    public function create(array $params = [])
    {
        $params = $this->addChainedParametersToParams($params, ['categoryId' => Categories::class]);

        if (! $this->hasKeys($params, ['categoryId'])) {
            throw new MissingParametersException(__METHOD__, ['categoryId']);
        }

        return $this->traitCreate($params);
    }

    /**
     * @inheritdoc
     */
    public function getRoute($name, array $params = [])
    {
        if ($name === 'create') {
            return parent::getRoute($name, ['categoryId' => $params['categoryId']]);
        }

        $routesWithLocale = ['findAll', 'find', 'update'];

        $locale = $this->getLocale();
        if (in_array($name, $routesWithLocale) && isset($locale)) {
            $originalResourceName = $this->resourceName;
            $this->resourceName   = "help_center/{$locale}/sections";

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
     * Updates a sections source_locale property
     *
     * @param $sectionId    The section to update
     * @param $sourceLocale The new source_locale
     *
     * @return array
     * @throws \Zendesk\API\Exceptions\RouteException
     */
    public function updateSourceLocale($sectionId, $sourceLocale)
    {
        if (empty($sectionId)) {
            $sectionId = $this->getChainedParameter(get_class($this));
        }

        return $this->client->put(
            $this->getRoute(__FUNCTION__, ['sectionId' => $sectionId]),
            ['section_locale' => $sourceLocale]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getValidSubResources()
    {
        return [
            'articles'            => Articles::class,
        ];
    }
}
