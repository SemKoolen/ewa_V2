<?php


namespace App\Controller;


use App\Entity\Contact;
use App\Entity\Document;
use App\Entity\Partner;
use App\Entity\Post;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class BezoekerController extends AbstractController
{
    private EntityManagerInterface $em;
    private SerializerInterface $serializer;
    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->em = $em;
        $this->serializer = $serializer;
    }
    /**
     * @Route("/", name="homeCreate")
     */
    function indexAction(Request $request, MailerInterface $mailer)
    {
        $nieuwsberichten = $this->em->getRepository(Post::class)->findLatest();
        $partners=$this->em->getRepository(Partner::class)->findAll();
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {

// create new contact
            $contact = $form->getData();
            $name = $form->get('name')->getData();
            if (empty($name)) {
                throw new BadRequestHttpException('name cannot be empty');
            }
            $emailAddress = $form->get('email')->getData();
            if (empty($emailAddress)) {
                throw new BadRequestHttpException('email cannot be empty');
            }
            $message = $form->get('message')->getData();
            if (empty($message)) {
                throw new BadRequestHttpException('message cannot be empty');
            }
// contact wishes to receive newsletter
            $subscribed = $form->get('subscribed')->getData();
            $contact->setName($name);
            $contact->setEmail($emailAddress);
            $contact->setMessage($message);
            $contact->setSubscribed($subscribed);
// make email
            $html = "<p>Beste mevrouw A. Waarts, </p>
                     <p>U heeft een bericht ontvangen via de website van EWA Haaglanden van $emailAddress.  </p>
                     <p>$message</p>";
            
            if ($subscribed == true) {
                $html .= "<p>Deze persoon heeft zich ook aangemeld voor de nieuwsbrief</p>";
            }

            $email = (new Email())
                ->from($emailAddress)
                ->to('ewahaaglanden@rocmondriaan.nl')
                ->subject('E-mail van EWA Haaglanden met bericht')
                ->html($html);

            $mailer->send($email);
            $email = (new TemplatedEmail())
                ->from('no-reply@ewahaaglanden.nl')
                ->to($emailAddress)
                ->subject('Uw bericht is ontvangen')
                ->htmlTemplate('emails/registration.html.twig')
                ->context([
                    'name' => $name,
                ]);

            $mailer->send($email);
            $this->addFlash('success',"Hartelijk dank, uw bericht is verzonden.");
            $this->getDoctrine()->getManager()->persist($contact);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('homeCreate');
        }
        return $this->render('bezoeker/home.html.twig', [
            'form' => $form->createView(),
            'nieuwsberichten' => $nieuwsberichten,
            'partners'=>$partners
        ]);

    }
    /**
     * @Route("/Nieuws/{id}", name="showNieuwsdetail")
     */
    function showNieuwsDetailsAction(Request $request, $id)
    {
        $nieuwsbericht = $this->em->getRepository(Post::class)->find($id);
        return $this->render('bezoeker/showNieuwsbericht.html.twig', [
            'nieuwsbericht' => $nieuwsbericht,
        ]);

    }
    /**
     * @Route("/Nieuws", name="showNieuws")
     */
    public function findAllNbAction()
    {
        $news = $this->em->getRepository(Post::class)->findBy([], ['id' => 'DESC']);
        foreach($news as $nw)
        {
            $nw->setContent(substr($nw->getContent(),0,200));
            $nw->setContent($nw->getContent() . "...");
        }

        return $this->render('bezoeker/showNieuwsberichten.html.twig', [
            'news' => $news,
        ]);
    }

    /**
     * @Route("/Partners", name="showPartners")
     */
    public function findAllPAction()
    {
        $partners = $this->em->getRepository(Partner::class)->findBy([], ['id' => 'DESC']);

        return $this->render('bezoeker/showPartners.html.twig', [
            'partners' => $partners,
        ]);
    }
    /**
     * @Route("/Informatie", name="showInformatie")
     */
    public function findAllIAction()
    {
        $information = $this->em->getRepository(Document::class)->findBy([], ['id' => 'DESC']);

        return $this->render('bezoeker/showInformatie.html.twig', [
            'informatietotaal' => $information,
        ]);
    }

    /**
     * @Route("/Informatie/{name}", name="showInformatieSection")
     */
    public function findAllIActionJump(Request $request, $name)
    {
        $informatie = $this->em->getRepository(Document::class)->findOneBy(['name' => $name]);

        return $this->render('bezoeker/showInformatie.html.twig', [
            'informatietotaal' => [$informatie],
            'name' => $name,
        ]);
    }
}