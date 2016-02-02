<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FormaLibre\BulletinBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use FormaLibre\BulletinBundle\Entity\Periode;
use Claroline\CursusBundle\Entity\CourseSession;

/**
 * @DI\Service("formalibre.manager.totaux_manager")
 */
class TotauxManager
{
    private $bulletinManager;
    private $om;
    private $pempRepo;
    private $pemdRepo;
    private $periodeRepo;

    /**
     * @DI\InjectParams({
     *     "bulletinManager" = @DI\Inject("formalibre.manager.bulletin_manager"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(BulletinManager $bulletinManager, ObjectManager $om)
    {
        $this->bulletinManager = $bulletinManager;
        $this->om = $om;

        $this->pempRepo = $om->getRepository('FormaLibreBulletinBundle:PeriodeEleveMatierePoint');
        $this->pemdRepo = $om->getRepository('FormaLibreBulletinBundle:PeriodeElevePointDiversPoint');
        $this->periodeRepo = $om->getRepository('FormaLibreBulletinBundle:Periode');
    }

    public function getTotalPeriode(Periode $periode, User $eleve)
    {
        $pemps = $this->bulletinManager->getPempsByEleveAndPeriode($eleve, $periode);
        $totalPoint = 0;
        $totalTotal = 0;

        foreach ($pemps as $pemp) {
            if ($pemp->getPoint() < 850){
                $totalPoint += $pemp->getPoint();
                $totalTotal += $pemp->getTotal();
            }
        }

        if ($totalTotal === 0) {
            $totalPourcentage = '0 %';
            $totalPourcentageAffiche = '0 %';
        } else {
            $totalPourcentageAffiche = round(($totalPoint / $totalTotal) * 100, 1).' %';
        if($periode->getTemplate()==="PeriodePrint"){
            $totalPourcentage = round(($totalPoint / $totalTotal) * 100, 1).' %';
        }
            else{    
                $totalPourcentage = round(($totalPoint*$periode->getCoefficient() / $totalTotal) * 100, 1).' %';
            }
            
        }

        return array('totalPoint' => $totalPoint, 'totalTotal' => $totalTotal, 'totalPourcentage' => $totalPourcentage, 'totalPourcentageAffiche'=>$totalPourcentageAffiche);
    }

    public function getTotalCoefficient(Periode $periode){
        
        $totalCoefficient=$periode->getCoefficient();
        
        if ($periode->getTemplate() === 'ExamPrint'){
            $totalCoefficient+=$periode->getOldPeriode1()->getCoefficient();  
            $totalCoefficient+=$periode->getOldPeriode2()->getCoefficient();   
        }
        elseif ($periode->getTemplate() === 'ExamPrintWithOnlyOnePeriodePrint'){
            $totalCoefficient+=$periode->getOldPeriode1()->getCoefficient();   
        }
        elseif ($periode->getTemplate() === 'FinalExamPrint'){
            $totalCoefficient+=$periode->getOldPeriode1()->getCoefficient();
            $totalCoefficient+=$periode->getOldPeriode2()->getCoefficient(); 
            $totalCoefficient+=$periode->getOldPeriode3()->getCoefficient(); 
            $totalCoefficient+=$periode->getOldPeriode4()->getCoefficient(); 
            $totalCoefficient+=$periode->getOldPeriode5()->getCoefficient(); 
        }
        
        return $totalCoefficient;  
    }
  
    public function getTotalPeriodes(User $eleve, Periode $periode)
    {
        $periodes = ($periode->getTemplate() === 'ExamPrintWithOnlyOnePeriodePrint') ?
                array($periode->getOldPeriode1(), $periode):
                array($periode->getOldPeriode1(), $periode->getOldPeriode2(), $periode);
        $totaux = array();
        $nbPeriodes = array();

        foreach ($periodes as $periode){
            $pemps = $this->bulletinManager->getPempsByEleveAndPeriode($eleve, $periode);

            foreach ($pemps as $key => $pemp){
                if (!isset($totaux[$key])) {
                    $totaux[$key] = 0;
                    $nbPeriodes[$key] = 0;
                }

                if ($pemp->getPourcentage() != 999 && $pemp->getPourcentage() != 888){
                    $totaux[$key] += $pemp->getPourcentage()*$periode->getCoefficient();
                    $nbPeriodes[$key]++;
                }
            }
        }

        foreach ($totaux as $key => $total) {

            if ($nbPeriodes[$key] > 0) {
                $totaux[$key] = round($total / $this->getTotalCoefficient($periode), 1);
            }
        }

        return $totaux;
    }

    public function getFinalTotalPeriodes(User $eleve)
    {
        $periodes = $this->periodeRepo->findAll();
        $totaux = array();
        $nbPeriodes = array();

        foreach ($periodes as $periode){
            $pemps = $this->bulletinManager->getPempsByEleveAndPeriode($eleve, $periode);

            foreach ($pemps as $key => $pemp){
                if (!isset($totaux[$key])) {
                    $totaux[$key] = 0;
                    $nbPeriodes[$key] = 0;
                }

                if ($pemp->getPourcentage() != 999 && $pemp->getPourcentage() != 888){
                    $totaux[$key] += $pemp->getPourcentage()*$periode->getCoefficient();
                    $nbPeriodes[$key]++;
                }
            }
        }

        foreach ($totaux as $key => $total) {

            if ($nbPeriodes[$key] > 0) {
                $totaux[$key] = round($total / $this->getTotalCoefficient($periode), 1);
            }
        }

        return $totaux;
    }

    public function getTotalPeriodesMatiere(User $eleve)
    {
        $periodes = $this->periodeRepo->findAll();
        $totaux = array();
        
        foreach ($periodes as $periode) {
            $pemps = $this->bulletinManager->getPempsByEleveAndPeriode($eleve, $periode);

            foreach ($pemps as $pemp){
                $matiere = $pemp->getMatiere();
                $matiereOptions = $this->bulletinManager->getOptionsByMatiere($matiere);
                $matiereId = $matiere->getId();
                
                if (!isset($totaux[$matiereId])) {
                    $totaux[$matiereId] = array();
                    $totaux[$matiereId]['name'] = $matiere->getCourse()->getTitle();
                    $totaux[$matiereId]['pourcentage'] = 0;
                    $totaux[$matiereId]['nbPeriodes'] = 0;
                    $totaux[$matiereId]['color'] = $matiereOptions->getColor();
                }
                
                if ($pemp->getPourcentage() != 999 && $pemp->getPourcentage() != 888){
                    $totaux[$matiereId]['pourcentage'] += $pemp->getPourcentage();
                    $totaux[$matiereId]['nbPeriodes']++;
                }
            }
        }

        foreach ($totaux as $key => $total) {
            $pourcentage = $total['pourcentage'];
            $nbPeriodes = $total['nbPeriodes'];

            if ($nbPeriodes > 0) {
                $totaux[$key]['value'] = round($pourcentage / $nbPeriodes, 1);
            }
        }

        return $totaux;
    }

    public function getDataChart(User $eleve, $isCeb = true)
    {
        $periodes = $this->periodeRepo->findAll();
        $periodeNames = array();
        $matCeb = array("Français", "Math", "Néerlandais", "Histoire", "Géographie", "Sciences");

        foreach ($periodes as $periode) {
            $periodeNames[] = $periode->getName();
        }
        $data = new \StdClass();
        $data->labels = $periodeNames;
        $data->datasets = array();

        //créons les matières avec les moyens du bord !
        $periode = $this->periodeRepo->findOneById(1);
        $pemps = $this->bulletinManager->getPempsByEleveAndPeriode($eleve, $periode);

        foreach ($pemps as $pemp) {
            $matiere = $pemp->getMatiere();
            $matiereOptions = $this->bulletinManager->getOptionsByMatiere($matiere);
            $matiereName = $matiere->getCourse()->getTitle();
            $matiereColor = $matiereOptions->getColor();
            $pempInCeb = in_array($matiereName, $matCeb) ? true: false;
            $object = new \StdClass();
            $object->label = $matiereName;
            $object->fillColor = $matiereColor;
            $object->pointColor = $matiereColor;
            $object->strokeColor = $matiereColor;
            $object->pointStrokeColor = $matiereColor;
            $object->pointHighlightFill = $matiereColor;
            $object->pointHighlightStroke = $matiereColor;
            $object->data = $this->getPourcentageMatierePeriode($matiere, $eleve);

            if ($pempInCeb && $isCeb) {
                $data->datasets[] = $object;
            }

            if (!$pempInCeb && !$isCeb) {
                $data->datasets[] = $object;
            }
        }
        $redLines = array();
        
        foreach ($periodes as $periode) {
            $redLines[] = 50;
        }
        $object = new \StdClass();
        $object->label = 'Séparateur';
        $object->fillColor = '#ff0000';
        $object->pointColor = 'rgba(0,0,0,0)';
        $object->strokeColor = '#ff0000';
        $object->pointStrokeColor = 'rgba(0,0,0,0)';
        $object->pointHighlightFill = 'rgba(0,0,0,0)';
        $object->pointHighlightStroke = 'rgba(0,0,0,0)';
        $object->data = $redLines;
        $data->datasets[] = $object;

        return json_encode($data);
    }

    private function getPourcentageMatierePeriode(CourseSession $matiere, User $eleve)
    {
        $periodes = $this->periodeRepo->findAll();
        $pourcPeriode = array();

        foreach ($periodes as $periode){
            $pemp = $this->pempRepo->findPeriodeMatiereEleve($periode, $eleve, $matiere);

            if (is_null($pemp) || $pemp->getPourcentage() == 999 || $pemp->getPourcentage() == 888){
                $pourcPeriode[] = '';
            } else{
                $pourcPeriode[] = round($pemp->getPourcentage(), 1);
            }

        }

        return $pourcPeriode;
    }

    public function getMoyennePresence(User $user)
    {
        $results = array();
        $periodes = $this->periodeRepo->findNonOnlyPointPeriodes();
        $nbPeriodes = count($periodes);
        $points = $this->pempRepo->findPEMPByUserAndNonOnlyPointPeriode($user);

        foreach ($points as $point) {
            $presence = is_null($point->getPresence()) ? 0 : $point->getPresence();
            $matiere = $point->getMatiere();
            $matiereId = $matiere->getId();

            if (!isset($results[$matiereId])) {
                $results[$matiereId] = array();
                $results[$matiereId]['matiere'] = $matiere->getCourse()->getTitle();
                $results[$matiereId]['presence'] = $presence;
            } else {
                $results[$matiereId]['presence'] += $presence;
            }
        }

        foreach ($results as $key => $result) {
            $results[$key]['presence'] /= $nbPeriodes;
        }

        return $results;
    }

    public function getMoyenneComportement(User $user)
    {
        $results = array();
        $periodes = $this->periodeRepo->findNonOnlyPointPeriodes();
        $nbPeriodes = count($periodes);
        $points = $this->pempRepo->findPEMPByUserAndNonOnlyPointPeriode($user);

        foreach ($points as $point) {
            $comportement = is_null($point->getComportement()) ?
                0 :
                $point->getComportement();
            $matiere = $point->getMatiere();
            $matiereId = $matiere->getId();

            if (!isset($results[$matiereId])) {
                $results[$matiereId] = array();
                $results[$matiereId]['matiere'] = $matiere->getCourse()->getTitle();
                $results[$matiereId]['comportement'] = $comportement;
            } else {
                $results[$matiereId]['comportement'] += $comportement;
            }
        }

        foreach ($results as $key => $result) {
            $results[$key]['comportement'] /= $nbPeriodes;
        }

        return $results;
    }

    public function getMoyennePointsDivers(User $user)
    {
        $results = array();
        $pointsDiversPoints = $this->pemdRepo->findPEPDPByUserAndNonOnlyPointPeriode($user);

        foreach ($pointsDiversPoints as $pointsDiversPoint) {
            $pointDivers = $pointsDiversPoint->getDivers();
            $pointDiversId = $pointDivers->getId();
            $withTotal = $pointDivers->getWithTotal();
            $points = is_null($pointsDiversPoint->getPoint()) ?
                0 :
                $pointsDiversPoint->getPoint();
            $total = is_null($pointsDiversPoint->getTotal()) ?
                0 :
                $pointsDiversPoint->getTotal();

            if (!isset($results[$pointDiversId])) {
                $results[$pointDiversId] = array();
                $results[$pointDiversId]['name'] = $pointDivers->getName();
                $results[$pointDiversId]['withTotal'] = $withTotal;
                $results[$pointDiversId]['points'] = $points;
                $results[$pointDiversId]['total'] = $total;
            } else {
                $results[$pointDiversId]['points'] += $points;
                $results[$pointDiversId]['total'] += $total;
            }
        }

        foreach ($results as $key => $result) {

            if ($results[$key]['withTotal']) {
                $results[$key]['value'] = $result['points'] . ' / ' . $result['total'];
            } else {
                $results[$key]['value'] = $result['points'];
            }
        }

        return $results;
    }
}
