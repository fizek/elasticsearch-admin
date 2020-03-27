<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateSnapshotType;
use App\Model\CallModel;
use App\Model\ElasticsearchSnapshotModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SnapshotsController extends AbstractAppController
{
    /**
     * @Route("snapshots", name="snapshots")
     */
    public function index(Request $request): Response
    {
        $repositories = $this->callManager->selectRepositories();
        $snapshots = [];

        foreach ($repositories as $repository) {
            $call = new CallModel();
            $call->setPath('/_snapshot/'.$repository.'/_all');
            $rows = $this->callManager->call($call);

            foreach ($rows['snapshots'] as $row) {
                $row['repository'] = $repository;
                $snapshots[] = $row;
            }
        }

        return $this->renderAbstract($request, 'Modules/snapshots/snapshots_index.html.twig', [
            'snapshots' => $this->paginatorManager->paginate([
                'route' => 'snapshots',
                'route_parameters' => [],
                'total' => count($snapshots),
                'rows' => $snapshots,
                'page' => 1,
                'size' => count($snapshots),
            ]),
        ]);
    }

    /**
     * @Route("/snapshots/create", name="snapshots_create")
     */
    public function create(Request $request): Response
    {
        $repositories = $this->callManager->selectRepositories();
        $indices = $this->callManager->selectIndices();

        $snapshot = new ElasticsearchSnapshotModel();
        if ($request->query->get('repository')) {
            $snapshot->setRepository($request->query->get('repository'));
        }
        if ($request->query->get('index')) {
            $snapshot->setIndices([$request->query->get('index')]);
        }
        $form = $this->createForm(CreateSnapshotType::class, $snapshot, ['repositories' => $repositories, 'indices' => $indices]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $body = [
                    'ignore_unavailable' => $snapshot->getIgnoreUnavailable(),
                    'include_global_state' => $snapshot->getIncludeGlobalState(),
                ];
                if ($snapshot->getIndices()) {
                    $body['indices'] = implode(',', $snapshot->getIndices());
                }
                $call = new CallModel();
                $call->setMethod('PUT');
                $call->setPath('/_snapshot/'.$snapshot->getRepository().'/'.$snapshot->getName());
                $call->setBody($body);
                $this->callManager->call($call);

                $this->addFlash('success', 'snapshots_create');

                return $this->redirectToRoute('snapshots_read', ['repository' => $snapshot->getRepository(), 'snapshot' => $snapshot->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/snapshots/snapshots_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/snapshots/{repository}/{snapshot}", name="snapshots_read")
     */
    public function read(Request $request, string $repository, string $snapshot): Response
    {
        $call = new CallModel();
        $call->setPath('/_snapshot/'.$repository.'/'.$snapshot);
        $snapshot = $this->callManager->call($call);

        if ($snapshot) {
            return $this->renderAbstract($request, 'Modules/snapshots/snapshots_read.html.twig', [
                'repository' => $repository,
                'snapshot' => $snapshot['snapshots'][0],
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/snapshots/{repository}/{snapshot}/failures", name="snapshots_read_failures")
     */
    public function readFailures(Request $request, string $repository, string $snapshot): Response
    {
        $call = new CallModel();
        $call->setPath('/_snapshot/'.$repository.'/'.$snapshot);
        $snapshot = $this->callManager->call($call);

        if ($snapshot) {
            $nodes = [];

            $call = new CallModel();
            $call->setPath('/_nodes');
            $rows = $this->callManager->call($call);

            foreach ($rows['nodes'] as $k => $row) {
                $nodes[$k] = $row['name'];
            }

            return $this->renderAbstract($request, 'Modules/snapshots/snapshots_read_failures.html.twig', [
                'repository' => $repository,
                'snapshot' => $snapshot['snapshots'][0],
                'nodes' => $nodes,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/snapshots/{repository}/{snapshot}/delete", name="snapshots_delete")
     */
    public function delete(Request $request, string $repository, string $snapshot): Response
    {
        $call = new CallModel();
        $call->setMethod('DELETE');
        $call->setPath('/_snapshot/'.$repository.'/'.$snapshot);
        $this->callManager->call($call);

        $this->addFlash('success', 'snapshots_delete');

        return $this->redirectToRoute('snapshots', []);
    }
}
