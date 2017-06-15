<?php
/**
 * This file contains only the ApiController class.
 */

namespace AppBundle\Controller;

use AppBundle\Helper\AutomatedEditsHelper;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Debug\Exception\FatalErrorException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Xtools\ProjectRepository;

/**
 * Serves the external API of XTools.
 */
class ApiController extends FOSRestController
{
    /**
     * Get domain name, URL, and API URL of the given project.
     * @Rest\Get("/api/normalizeProject/{project}")
     * @param string $project Project database name, URL, or domain name.
     * @return View
     */
    public function normalizeProject($project)
    {
        $proj = ProjectRepository::getProject($project, $this->container);

        if (!$proj->exists()) {
            return new View(
                [
                    'error' => "$project is not a valid project",
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        return new View(
            [
                'domain' => $proj->getDomain(),
                'url' => $proj->getUrl(),
                'api' => $proj->getApiUrl(),
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Get all namespaces of the given project.
     * @Rest\Get("/api/namespaces/{project}")
     * @param string $project The project name.
     * @return View
     */
    public function namespaces($project)
    {
        $proj = ProjectRepository::getProject($project, $this->container);

        if (!$proj->exists()) {
            return new View(
                [
                    'error' => "$project is not a valid project",
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        return new View(
            [
                'api' => $proj->getApiUrl(),
                'namespaces' => $proj->getNamespaces(),
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Get non-automated edits for the given user.
     * @Rest\Get("/api/nonautomated_edits/{project}/{username}/{namespace}/{offset}/{format}")
     * @param string $project
     * @param string $username
     * @param string $namespace
     * @param int $offset
     * @param string $format
     * @return View
     */
    public function nonautomatedEdits($project, $username, $namespace, $offset = 0, $format = 'json')
    {
        $twig = $this->container->get('twig');
        $aeh = $this->get('app.automated_edits_helper');
        $data = $aeh->getNonautomatedEdits($project, $username, $namespace, $offset);

        if ($format === 'html') {
            $data = $twig->render('api/automated_edits.html.twig', [
                'edits' => $data,
                'projectUrl' =>  "https://$project",
            ]);
        }

        return new View(
            ['data' => $data],
            Response::HTTP_OK
        );
    }
}
