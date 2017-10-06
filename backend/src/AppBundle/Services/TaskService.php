<?php

namespace AppBundle\Services;

use AppBundle\Entity\Tasks;

class TaskService
{
    public $em;

    public function __construct($manager)
    {
        $this->em = $manager;
    }

    public function create($json, $token, $jwtAuth, $id = null)
    {
        $authCheck = $jwtAuth->checkToken($token);
        if (!$authCheck) {
            throw new \Exception('error: Sin Autorizacion.', 403);
        }
        if ($json === null) {
            throw new \Exception('error: Sin datos para actualizar la tarea.', 400);
        }
        $params = json_decode($json);
        $identity = $jwtAuth->checkToken($token, true);
        $userId = ($identity->sub != null) ? $identity->sub : null;
        $title = isset($params->title) ? $params->title : null;
        $description = isset($params->description) ? $params->description : null;
        $status = isset($params->status) ? $params->status : null;
        if ($userId === null || $title === null) {
            throw new \Exception('error: Los datos de la tarea no son validos.', 400);
        }
        $user = $this->em->getRepository('AppBundle:Users')->findOneBy(['id' => $userId]);
        if ($id === null) {
            $data = $this->createTask($user, $title, $description, $status);
        } else {
            $data = $this->updateTask($id, $identity, $title, $description, $status);
        }

        return $data;
    }

    private function createTask($user, $title, $description, $status)
    {
        $task = new Tasks();
        $task->setUser($user);
        $task->setTitle($title);
        $task->setDescription($description);
        $task->setStatus($status);
        $task->setCreatedAt(new \DateTime("now"));
        $task->setUpdatedAt(new \DateTime("now"));
        $this->em->persist($task);
        $this->em->flush();
        $data = [
            'status' => 'success',
            'code' => 200,
            'msg' => 'Tarea creada.',
            'task' => $task,
        ];

        return $data;
    }

    private function updateTask($id, $identity, $title, $description, $status)
    {
        $task = $this->em->getRepository('AppBundle:Tasks')->findOneBy(['id' => $id]);
        if (!isset($identity->sub) || $identity->sub != $task->getUser()->getId()) {
            throw new \Exception('error: Los datos de la tarea no son validos. [Owner task error]', 400);
        }
        $task->setTitle($title);
        $task->setDescription($description);
        $task->setStatus($status);
        $task->setUpdatedAt(new \DateTime("now"));
        $this->em->persist($task);
        $this->em->flush();
        $data = [
            'status' => 'success',
            'code' => 200,
            'msg' => 'Tarea actualizada.',
            'task' => $task,
        ];

        return $data;
    }

    public function getTasks($jwtAuth, $token, $paginator, $page)
    {
        $authCheck = $jwtAuth->checkToken($token);
        if (!$authCheck) {
            throw new \Exception('error: Sin Autorizacion.', 403);
        }
        $identity = $jwtAuth->checkToken($token, true);
        $dql = "SELECT t FROM AppBundle:Tasks t WHERE t.user = $identity->sub ORDER BY t.id ASC";
        $query = $this->em->createQuery($dql);
        $itemsPerPage = 10;
        $pagination = $paginator->paginate($query, $page, $itemsPerPage);
        $totalItemsCount = $pagination->getTotalItemCount();
        $data = [
            'status' => 'success',
            'code' => 200,
            'totalItemsCount' => $totalItemsCount,
            'actual_page' => $page,
            'itemsPerPage' => $itemsPerPage,
            'totalPages' => ceil($totalItemsCount / $itemsPerPage),
            'tasks' => $pagination,
        ];

        return $data;
    }

    public function getTask($jwtAuth, $token, $id)
    {
//        $helpers = $this->get(Helpers::class);
//        $jwtAuth = $this->get(JwtAuth::class);
//        $token = $request->get('authorization', null);
        $authCheck = $jwtAuth->checkToken($token);
        if ($authCheck === true) {
            $identity = $jwtAuth->checkToken($token, true);
            $em = $this->em;
            $task = $em->getRepository('AppBundle:Tasks')->findOneBy(['id' => $id]);
            if ($task && is_object($task) && $identity->sub == $task->getUser()->getId()) {
                $status = 200;
                $data = [
                    'status' => 'success',
                    'code' => $status,
                    'task' => $task,
                ];
            } else {
                $status = 404;
                $data = [
                    'status' => 'error',
                    'code' => $status,
                    'msg' => 'Task not found.',
                ];
            }
        } else {
            $status = 403;
            $data = [
                'status' => 'error',
                'code' => $status,
                'msg' => 'Sin Autorizacion.',
            ];
        }

        return $data;
//        return $helpers->json($data, $status);
    }
}
