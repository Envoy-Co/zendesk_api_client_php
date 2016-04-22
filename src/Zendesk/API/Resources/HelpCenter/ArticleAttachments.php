<?php

namespace Zendesk\API\Resources\HelpCenter;

use GuzzleHttp\Psr7\LazyOpenStream;
use Zendesk\API\Exceptions\CustomException;
use Zendesk\API\Exceptions\MissingParametersException;
use Zendesk\API\Http;

/**
 * Class ArticleAttachments
 * https://developer.zendesk.com/rest_api/docs/help_center/article_attachments
 */
class ArticleAttachments extends ResourceAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function setUpRoutes()
    {
        $this->setRoutes([
            'create' => "help_center/articles/{articleId}/attachments.json",
            'createUnassociated' => "help_center/articles/attachments.json"
        ]);
    }

    /**
     * Create a new attachment within an article
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

        if (! $this->hasKeys($params, ['file', 'articleId'])) {
            throw new MissingParametersException(__METHOD__, ['file', 'articleId']);
        } elseif (! file_exists($params['file'])) {
            throw new CustomException('File ' . $params['file'] . ' could not be found in ' . __METHOD__);
        }

        if (! isset($params['name'])) {
            $params['name'] = basename($params['file']);
        }

        if (! isset($params['inline'])) {
            $params['inline'] = false;
        }

        $queryParams = [
            'inline' => $params['inline'] ? 'true' : 'false'
        ];

        $response = Http::send(
            $this->client,
            $this->getRoute(__FUNCTION__, ['articleId' => $params['articleId']]),
            [
                'method'      => 'POST',
                'queryParams' => $queryParams,
                'multipart'   => [
                    [
                        'name'     => 'file',
                        'contents' => new LazyOpenStream($params['file'], 'r'),
                        'filename' => $params['name']
                    ]
                ]
            ]
        );

        return $response;

    }

    /**
     * Create a new unassociated attachment
     *
     * @param array $params
     *
     * @throws ResponseException
     * @throws \Exception
     * @return mixed
     */
    public function createUnassociated(array $params = [])
    {
        if (! $this->hasKeys($params, ['file'])) {
            throw new MissingParametersException(__METHOD__, ['file']);
        } elseif (! file_exists($params['file'])) {
            throw new CustomException('File ' . $params['file'] . ' could not be found in ' . __METHOD__);
        }

        if (! isset($params['name'])) {
            $params['name'] = basename($params['file']);
        }

        if (! isset($params['inline'])) {
            $params['inline'] = false;
        }

        $queryParams = [
            'inline' => $params['inline'] ? 'true' : 'false'
        ];

        $response = Http::send(
            $this->client,
            $this->getRoute(__FUNCTION__),
            [
                'method'      => 'POST',
                'queryParams' => $queryParams,
                'multipart'   => [
                    [
                        'name'     => 'file',
                        'contents' => new LazyOpenStream($params['file'], 'r'),
                        'filename' => $params['name']
                    ]
                ]
            ]
        );

        return $response;

    }

}
