<?php

namespace Main\SiteBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ClickRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ClickRepository extends EntityRepository
{
	public function findDistByYear($field, $year, $numberOfEntries){
		if ( $year != 0) {
			$query = $this->_em->createQuery('SELECT DISTINCT c.'. $field .' FROM MainSiteBundle:Click c WHERE c.date BETWEEN :start AND :end');
			$query->setParameter('start', new \Datetime($year.'-01-01')); 
      		$query->setParameter('end',   new \Datetime($year.'-12-31'));
		}else{
			$query = $this->_em->createQuery('SELECT DISTINCT c.'. $field .' FROM MainSiteBundle:Click c');
		}
		

		$r = $query->getArrayResult();
		$a = array();

		foreach ($r as $key => $item) {
            $number = $this->countByFieldAndValue($field, $item[$field]);
            $item['number'] = $number;
            $a[] = $item;
        }

        $a2 = $this->array_sort($a, 'number', SORT_DESC); // Sort by oldest first

        if ($numberOfEntries > count($a2) || $numberOfEntries == 0) {
        	for ($i = 0; $i < count($a2); $i++) { 
        		$a3[] = $a2[$i];
        	}
        }else {
			$t = 0;

        	for ($i = 0; $i < ($numberOfEntries - 1) ; $i++) { 
        		$a3[] = $a2[$i];
        	}

	        for ($i = ($numberOfEntries - 1); $i < count($a2); $i++) { 
	        	$t = $t + $a2[$i]['number'];
	        }

	        $a3[] = array($field => 'Autres', 'number' => $t);
        }

  		return $a2;
	}

	public function findDistRefByLink($link, $numberOfEntries){

		$qb = $this->_em->createQueryBuilder();
        $date = new \Datetime(date('d-m-Y'));
        $date->sub(new \DateInterval('P14D'));
 
        $qb->select('c')
       		->from('MainSiteBundle:Click', 'c')
       		->where('c.date > :start AND c.link = :link')
         	->setParameter('start', $date)
         	->setParameter('link', $link)
       		->orderBy('c.date', 'asc');
 
        $result = $qb->getQuery()
               	     ->getResult();

        $click_array = array();

        foreach ($result as $key => $click) {
        	$click_array[$click->getReferrer()]['name'] = $click->getReferrer();
        	$click_array[$click->getReferrer()]['number'] = 0;
        }

        foreach ($result as $key => $click) {
        	if (isset($click_array[$click->getReferrer()])) {
        		$click_array[$click->getReferrer()]['number'] = $click_array[$click->getReferrer()]['number'] + 1;
        	}
        }

        if (count($click_array) > $numberOfEntries) {
        	$autre = 0;
        	for ($i = ($numberOfEntries-1); $i < count($click_array-1); $i++) { 
        		$autre = $autre + $click_array[$i]['number'];
        	}

        	array_splice($click_array, $numberOfEntries-1, count($click_array), $autre);
        }

        return $click_array;
	}

	public function findDistCountryByLink($link, $numberOfEntries){

		$qb = $this->_em->createQueryBuilder();
        $date = new \Datetime(date('d-m-Y'));
        $date->sub(new \DateInterval('P14D'));
 
        $qb->select('c')
       		->from('MainSiteBundle:Click', 'c')
       		->where('c.date > :start AND c.link = :link')
         	->setParameter('start', $date)
         	->setParameter('link', $link)
       		->orderBy('c.date', 'asc');
 
        $result = $qb->getQuery()
               	     ->getResult();

        $click_array = array();

        foreach ($result as $key => $click) {
        	$click_array[$click->getCountry()]['name'] = $click->getCountry();
        	$click_array[$click->getCountry()]['number'] = 0;
        }

        foreach ($result as $key => $click) {
        	if (isset($click_array[$click->getCountry()])) {
        		$click_array[$click->getCountry()]['number'] = $click_array[$click->getCountry()]['number'] + 1;
        	}
        }

        if (count($click_array) > $numberOfEntries) {
        	$autre = 0;
        	for ($i = ($numberOfEntries-1); $i < count($click_array-1); $i++) { 
        		$autre = $autre + $click_array[$i]['number'];
        	}

        	array_splice($click_array, $numberOfEntries-1, count($click_array), $autre);
        }

        return $click_array;
	}

	public function countByFieldAndValue($field, $value){
		$query = $this->_em->createQuery('SELECT COUNT(c) FROM MainSiteBundle:Click c WHERE c.'. $field .' = :value');
		$query->setParameter('value', $value);
		$results = $query->getArrayResult();

  		return $results[0][1];
	}

	public function countByFieldLinkAndValue($field, $link, $value){
		$query = $this->_em->createQuery('SELECT COUNT(c) FROM MainSiteBundle:Click c JOIN MainSiteBundle:Link l WITH l.id = :link WHERE c.'. $field .' = :value ');
		$query->setParameter('value', $value);
		$query->setParameter('link', $link);
		$results = $query->getArrayResult();

  		return $results[0][1];
	}

	public function clickRecentByLink($link)
    {
        $qb = $this->_em->createQueryBuilder();
        $date = new \Datetime(date('d-m-Y'));
        $date->sub(new \DateInterval('P14D'));
 
        $qb->select('c')
       		->from('MainSiteBundle:Click', 'c')
       		->where('c.date > :start AND c.link = :link')
         	->setParameter('start', $date)
         	->setParameter('link', $link)
       		->orderBy('c.date', 'asc');
 
        return $qb->getQuery()
               	  ->getResult();
    }

	private function array_sort($array, $on, $order=SORT_ASC)
	{
	    $new_array = array();
	    $sortable_array = array();

	    if (count($array) > 0) {
	        foreach ($array as $k => $v) {
	            if (is_array($v)) {
	                foreach ($v as $k2 => $v2) {
	                    if ($k2 == $on) {
	                        $sortable_array[$k] = $v2;
	                    }
	                }
	            } else {
	                $sortable_array[$k] = $v;
	            }
	        }

	        switch ($order) {
	            case SORT_ASC:
	                asort($sortable_array);
	            break;
	            case SORT_DESC:
	                arsort($sortable_array);
	            break;
	        }

	        foreach ($sortable_array as $k => $v) {
	            $new_array[$k] = $array[$k];
	        }
	    }

	    return $new_array;
	}
}
