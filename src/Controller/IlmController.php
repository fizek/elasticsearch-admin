<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateIlmPolicyType;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchRepositoryManager;
use App\Model\CallModel;
use App\Model\ElasticsearchIlmPolicyModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class IlmController extends AbstractAppController
{
    public function __construct(ElasticsearchIndexManager $elasticsearchIndexManager, ElasticsearchRepositoryManager $elasticsearchRepositoryManager)
    {
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
        $this->elasticsearchRepositoryManager = $elasticsearchRepositoryManager;
    }

    /**
     * @Route("/ilm", name="ilm")
     */
    public function index(Request $request): Response
    {
        $policies = [];

        $call = new CallModel();
        $call->setPath('/_ilm/policy');
        $rows = $this->callManager->call($call);

        foreach ($rows as $k => $row) {
            $row['name'] = $k;
            $policies[] = $row;
        }

        return $this->renderAbstract($request, 'Modules/ilm/ilm_index.html.twig', [
            'policies' => $this->paginatorManager->paginate([
                'route' => 'policies',
                'route_parameters' => [],
                'total' => count($policies),
                'rows' => $policies,
                'page' => 1,
                'size' => count($policies),
            ]),
        ]);
    }

    /**
     * @Route("/ilm/stats", name="ilm_stats")
     */
    public function stats(Request $request): Response
    {
        $call = new CallModel();
        $call->setPath('/_ilm/stats');
        $stats = $this->callManager->call($call);

        return $this->renderAbstract($request, 'Modules/ilm/ilm_stats.html.twig', [
            'stats' => $stats,
        ]);
    }

    /**
     * @Route("/ilm/status", name="ilm_status")
     */
    public function status(Request $request): Response
    {
        $call = new CallModel();
        $call->setPath('/_ilm/status');
        $status = $this->callManager->call($call);

        return $this->renderAbstract($request, 'Modules/ilm/ilm_status.html.twig', [
            'status' => $status,
        ]);
    }

    /**
     * @Route("/ilm/start", name="ilm_start")
     */
    public function start(Request $request): Response
    {
        $call = new CallModel();
        $call->setMethod('POST');
        $call->setPath('/_ilm/start');
        $status = $this->callManager->call($call);

        $this->addFlash('success', 'success.ilm_start');

        return $this->redirectToRoute('ilm_status', []);
    }

    /**
     * @Route("/ilm/stop", name="ilm_stop")
     */
    public function stop(Request $request): Response
    {
        $call = new CallModel();
        $call->setMethod('POST');
        $call->setPath('/_ilm/stop');
        $status = $this->callManager->call($call);

        $this->addFlash('success', 'success.ilm_stop');

        return $this->redirectToRoute('ilm_status', []);
    }

    /**
     * @Route("/ilm/create", name="ilm_create")
     */
    public function create(Request $request): Response
    {
        $repositories = $this->elasticsearchRepositoryManager->selectRepositories();
        $indices = $this->elasticsearchIndexManager->selectIndices();

        $policyModel = new ElasticsearchIlmPolicyModel();
        if ($request->query->get('repository')) {
            $policyModel->setRepository($request->query->get('repository'));
        }
        if ($request->query->get('index')) {
            $policyModel->setIndices([$request->query->get('index')]);
        }
        $form = $this->createForm(CreateIlmPolicyType::class, $policyModel, ['repositories' => $repositories, 'indices' => $indices]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = [
                    'schedule' => $policyModel->getSchedule(),
                    'name' => $policyModel->getSnapshotName(),
                    'repository' => $policyModel->getRepository(),
                ];
                if ($policyModel->getIndices()) {
                    $json['config']['indices'] = $policyModel->getIndices();
                } else {
                    $json['config']['indices'] = ['*'];
                }
                $json['config']['ignore_unavailable'] = $policyModel->getIgnoreUnavailable();
                $json['config']['partial'] = $policyModel->getPartial();
                $json['config']['include_global_state'] = $policyModel->getIncludeGlobalState();

                if ($policyModel->hasRetention()) {
                    $json['retention'] = $policyModel->getRetention();
                }

                $call = new CallModel();
                $call->setMethod('PUT');
                $call->setPath('/_ilm/policy/'.$policyModel->getName());
                $call->setJson($json);
                $this->callManager->call($call);

                $this->addFlash('success', 'success.ilm_create');

                return $this->redirectToRoute('ilm_read', ['name' => $policyModel->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/ilm/ilm_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ilm/{name}", name="ilm_read")
     */
    public function read(Request $request, string $name): Response
    {
        $call = new CallModel();
        $call->setPath('/_ilm/policy/'.$name);
        $policy = $this->callManager->call($call);
        $policy = $policy[$name];
        $policy['name'] = $name;

        if ($policy) {
            return $this->renderAbstract($request, 'Modules/ilm/ilm_read.html.twig', [
                'policy' => $policy,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/ilm/{name}/history", name="ilm_read_history")
     */
    public function readHistory(Request $request, string $name): Response
    {
        $call = new CallModel();
        $call->setPath('/_ilm/policy/'.$name);
        $policy = $this->callManager->call($call);
        $policy = $policy[$name];
        $policy['name'] = $name;

        if ($policy) {
            return $this->renderAbstract($request, 'Modules/ilm/ilm_read_history.html.twig', [
                'policy' => $policy,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/ilm/{name}/stats", name="ilm_read_stats")
     */
    public function readStats(Request $request, string $name): Response
    {
        $call = new CallModel();
        $call->setPath('/_ilm/policy/'.$name);
        $policy = $this->callManager->call($call);
        $policy = $policy[$name];
        $policy['name'] = $name;

        if ($policy) {
            return $this->renderAbstract($request, 'Modules/ilm/ilm_read_stats.html.twig', [
                'policy' => $policy,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/ilm/{name}/update", name="ilm_update")
     */
    public function update(Request $request, string $name): Response
    {
        $call = new CallModel();
        $call->setPath('/_ilm/policy/'.$name);
        $policy = $this->callManager->call($call);
        $policy = $policy[$name];
        $policy['name'] = $name;

        if ($policy) {
            $repositories = $this->elasticsearchRepositoryManager->selectRepositories();
            $indices = $this->elasticsearchIndexManager->selectIndices();

            $policyModel = new ElasticsearchIlmPolicyModel();
            $policyModel->convert($policy);
            $form = $this->createForm(CreateIlmPolicyType::class, $policyModel, ['repositories' => $repositories, 'indices' => $indices, 'update' => true]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $json = [
                        'schedule' => $policyModel->getSchedule(),
                        'name' => $policyModel->getSnapshotName(),
                        'repository' => $policyModel->getRepository(),
                    ];
                    if ($policyModel->getIndices()) {
                        $json['config']['indices'] = $policyModel->getIndices();
                    } else {
                        $json['config']['indices'] = ['*'];
                    }
                    $json['config']['ignore_unavailable'] = $policyModel->getIgnoreUnavailable();
                    $json['config']['partial'] = $policyModel->getPartial();
                    $json['config']['include_global_state'] = $policyModel->getIncludeGlobalState();

                    if ($policyModel->hasRetention()) {
                        $json['retention'] = $policyModel->getRetention();
                    }

                    $call = new CallModel();
                    $call->setMethod('PUT');
                    $call->setPath('/_ilm/policy/'.$policyModel->getName());
                    $call->setJson($json);
                    $this->callManager->call($call);

                    $this->addFlash('success', 'success.ilm_update');

                    return $this->redirectToRoute('ilm_read', ['name' => $policyModel->getName()]);
                } catch (CallException $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }

            return $this->renderAbstract($request, 'Modules/ilm/ilm_update.html.twig', [
                'policy' => $policy,
                'form' => $form->createView(),
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/ilm/{name}/delete", name="ilm_delete")
     */
    public function delete(Request $request, string $name): Response
    {
        $call = new CallModel();
        $call->setMethod('DELETE');
        $call->setPath('/_ilm/policy/'.$name);
        $this->callManager->call($call);

        $this->addFlash('success', 'success.ilm_delete');

        return $this->redirectToRoute('ilm', []);
    }
}
