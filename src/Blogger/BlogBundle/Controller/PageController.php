<?php

// src/Blogger/BlogBundle/Controller/PageController.php

namespace Blogger\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Blogger\BlogBundle\Entity\Enquiry;
use Blogger\BlogBundle\Form\EnquiryType;

class PageController extends Controller {

    public function indexAction() {
        //$em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('BloggerBlogBundle:Blog');

        $blogs = $repository->getLatestBlogs();

        return $this->render('BloggerBlogBundle:Page:index.html.twig', array(
                    'blogs' => $blogs));
    }

    public function aboutAction() {
        return $this->render('BloggerBlogBundle:Page:about.html.twig');
    }

    public function contactAction() {
        $enquiry = new Enquiry();
        $form = $this->createForm(new EnquiryType(), $enquiry);

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                // Perform some action, such as sending an email
                // Redirect - This is important to prevent users re-posting
                // the form if they refresh the page

                $data = $form->getData();
                //var_dump($enquiry);exit;
                $nameFrom = $data->getName();
                $emailFrom = $data->getEmail();
                $subjectFrom = $data->getSubject();
                $bodyFrom = $data->getBody();

                $message = \Swift_Message::newInstance()
                        ->setSubject($subjectFrom)
                        ->setFrom($emailFrom)
                        ->setTo($this->container->getParameter('blogger_blog.emails.contact_email'))
                        ->setBody($this->renderView('BloggerBlogBundle:Page:contactEmail.txt.twig', array('enquiry' => $enquiry)));
                $this->get('mailer')->send($message);

                $this->get('session')->getFlashBag()->add('blogger-notice', 'Your contact enquiry was successfully sent. Thank you!');

                return $this->redirect($this->generateUrl('blogger_blog_contact'));
            }
        }

        return $this->render('BloggerBlogBundle:Page:contact.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    public function sidebarAction() {
        $em = $this->getDoctrine()->getManager();

        $commentLimit = $this->container->getParameter('blogger_blog.comments.latest_comment_limit');
        $latestComments = $em->getRepository('BloggerBlogBundle:Comment')->getLatestComments($commentLimit);

        $tags = $em->getRepository('BloggerBlogBundle:Blog')->getTags();

        $tagWeights = $em->getRepository('BloggerBlogBundle:Blog')->getTagWeights($tags);

        return $this->render('BloggerBlogBundle:Page:sidebar.html.twig', array(
                    'latestComments' => $latestComments,
                    'tags' => $tagWeights
        ));
    }

}
