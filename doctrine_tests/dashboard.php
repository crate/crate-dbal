<?php
/**
 * Licensed to CRATE Technology GmbH("Crate") under one or more contributor
 * license agreements.  See the NOTICE file distributed with this work for
 * additional information regarding copyright ownership.  Crate licenses
 * this file to you under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.  You may
 * obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.  See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 * However, if you have executed another commercial license agreement
 * with Crate these terms will supersede the license and you may use the
 * software solely pursuant to the terms of the relevant commercial agreement.
 */

require_once "bootstrap.php";

$theUserId = $argv[1];

$dql = "SELECT b FROM Bug b WHERE b.status = ?1 AND (b.engineer = ?2 OR b.reporter = ?2) ORDER BY b.created DESC";

$myBugs = $entityManager->createQuery($dql)
                        ->setParameter(1, 'OPEN')
                        ->setParameter(2, $theUserId)
                        ->setMaxResults(15)
                        ->getResult();

echo "You have created or assigned to " . count($myBugs) . " open bugs:\n\n";

foreach ($myBugs as $bug) {
    $myRole = "assigned";
    if ($bug->getReporter()->getId() == $theUserId) {
        $myRole = "reported";
    }
    echo "Bug: " . $bug->getId() . " - " . $bug->getDescription()." (".$myRole.")\n";
}