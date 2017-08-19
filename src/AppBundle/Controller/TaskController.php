<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use BackendBundle\Entity\Task;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class TaskController extends Controller
{
    public function newAction(Request $request, $id = null)
    {
        $helpers = $this->get(Helpers::class);
        $jwtAuth = $this->get(JwtAuth::class);

        $token = $request->get('authorization', null);
        $authCheck = $jwtAuth->checkToken($token);

        if ($authCheck == true) {
            $identity = $jwtAuth->checkToken($token, true);

            $json = $request->get('json', null);

            if ($json != null) {
                $params = json_decode($json);

                $createdAt = new \DateTime("now");
                $updateAt = new \DateTime("now");

                $userId = ($identity->sub != null) ? $identity->sub : null;
                $title = isset($params->title) ? $params->title : null;
                $description = isset($params->description) ? $params->description : null;
                $status = isset($params->status) ? $params->status : null;

                if ($userId != null && $title != null) {
                    $em = $this->getDoctrine()->getManager();

                    $user = $em->getRepository('BackendBundle:User')
                        ->findOneBy(['id' => $userId]);

                    if ($id == null) {
                        $task = new Task();
                        $task->setUser($user);
                        $task->setTitle($title);
                        $task->setDescription($description);
                        $task->setStatus($status);
                        $task->setCreatedAt($createdAt);
                        $task->setUpdatedAt($updateAt);

                        $em->persist($task);
                        $em->flush();

                        $data = [
                            'status' => 'success',
                            'code' => 200,
                            'msg' => 'Task Created.',
                            'task' => $task,
                        ];
                    } else {
                        $task = $em->getRepository('BackendBundle:Task')
                            ->findOneBy(['id' => $id]);

                        if (isset($identity->sub) && $identity->sub == $task->getUser()->getId()) {
                            $task->setTitle($title);
                            $task->setDescription($description);
                            $task->setStatus($status);
                            $task->setUpdatedAt($updateAt);

                            $em->persist($task);
                            $em->flush();

                            $data = [
                                'status' => 'success',
                                'code' => 200,
                                'msg' => 'Task Updated.',
                                'task' => $task,
                            ];
                        } else {
                            $data = [
                                'status' => 'error',
                                'code' => 400,
                                'msg' => 'Task validation failed. Owner task error.',
                           ];
                        }
                    }
                } else {
                    $data = [
                        'status' => 'error',
                        'code' => 400,
                        'msg' => 'Task validation failed.',
                   ];
                }
            } else {
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'msg' => 'Task  ....  exists.',
                ];
            }
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'msg' => 'Authorization Invalid.',
            ];
        }

        return $helpers->json($data);
    }

    public function tasksAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        $jwtAuth = $this->get(JwtAuth::class);

        $token = $request->get('authorization', null);
        $authCheck = $jwtAuth->checkToken($token);

        if ($authCheck == true) {
            $identity = $jwtAuth->checkToken($token, true);

            $em = $this->getDoctrine()->getManager();

            $dql = "SELECT t FROM BackendBundle:Task t WHERE t.user = $identity->sub ORDER BY t.id ASC";

            $query = $em->createQuery($dql);

            $page = $request->query->getInt('page', 1);
            $paginator = $this->get('knp_paginator');
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
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'msg' => 'Authorization Invalid.',
            ];
        }

        return $helpers->json($data);
    }

    public function taskAction(Request $request, $id = null)
    {
        $helpers = $this->get(Helpers::class);
        $jwtAuth = $this->get(JwtAuth::class);

        $token = $request->get('authorization', null);
        $authCheck = $jwtAuth->checkToken($token);

        if ($authCheck == true) {
            $identity = $jwtAuth->checkToken($token, true);

            $em = $this->getDoctrine()->getManager();

            $task = $em->getRepository('BackendBundle:Task')
                ->findOneBy(['id' => $id]);

            if ($task && is_object($task) && $identity->sub == $task->getUser()->getId()) {
                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'task' => $task,
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'code' => 404,
                    'msg' => 'Task not found.',
                ];
            }
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'msg' => 'Authorization Invalid.',
            ];
        }

        return $helpers->json($data);
    }

    public function searchAction(Request $request, $search = null)
    {
        $helpers = $this->get(Helpers::class);
        $jwtAuth = $this->get(JwtAuth::class);

        $token = $request->get('authorization', null);
        $authCheck = $jwtAuth->checkToken($token);

        if ($authCheck == true) {
            $identity = $jwtAuth->checkToken($token, true);

            $em = $this->getDoctrine()->getManager();

            $filter = $request->get('filter', null);
            if (empty($filter)) {
                $filter = null;
            } elseif ($filter == 1) {
                $filter = 'new';
            } elseif ($filter == 2) {
                $filter = 'todo';
            } else {
                $filter = 'finished';
            }

            $order = $request->get('order', null);
            if (empty($order) || $order == 2) {
                $order = 'DESC';
            } else {
                $order = 'ASC';
            }

            if ($search != null) {
                $dql = "
                    SELECT t FROM BackendBundle:Task t WHERE t.user = $identity->sub
                    AND t.title LIKE :search OR t.description LIKE :search
                ";
            } else {
                $dql = "SELECT t FROM BackendBundle:Task t WHERE t.user = $identity->sub ";
            }

            if ($filter != null) {
                $dql.= " AND t.status = :filter ";
            }

            $dql.= " ORDER BY t.id $order ";

            $query = $em->createQuery($dql);

            if (!empty($search)) {
                $query->setParameter('search', "%$search%");
            }
            if ($filter != null) {
                $query->setParameter('filter', "$filter");
            }

            $task = $query->getResult();

            $data = [
                'status' => 'success',
                'code' => 200,
                'data' => $task,
            ];
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'msg' => 'Authorization Invalid.',
            ];
        }

        return $helpers->json($data);
    }

    public function removeAction(Request $request, $id = null)
    {
        $helpers = $this->get(Helpers::class);
        $jwtAuth = $this->get(JwtAuth::class);

        $token = $request->get('authorization', null);
        $authCheck = $jwtAuth->checkToken($token);

        if ($authCheck == true) {
            $identity = $jwtAuth->checkToken($token, true);

            $em = $this->getDoctrine()->getManager();

            $task = $em->getRepository('BackendBundle:Task')
                ->findOneBy(['id' => $id]);

            if ($task && is_object($task) && $identity->sub == $task->getUser()->getId()) {
                $em->remove($task);
                $em->flush();

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'msg' => 'Task Deleted.',
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'code' => 404,
                    'msg' => 'Task not found.',
                ];
            }
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'msg' => 'Authorization Invalid.',
            ];
        }

        return $helpers->json($data);
    }
}