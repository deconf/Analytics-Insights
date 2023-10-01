<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 *
 * Modified by __root__ on 31-May-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Deconf\AIWP\Google\Service\GoogleAnalyticsAdmin;

class GoogleAnalyticsAdminV1alphaAudienceSequenceFilter extends \Deconf\AIWP\Google\Collection
{
  protected $collection_key = 'sequenceSteps';
  /**
   * @var string
   */
  public $scope;
  /**
   * @var string
   */
  public $sequenceMaximumDuration;
  protected $sequenceStepsType = GoogleAnalyticsAdminV1alphaAudienceSequenceFilterAudienceSequenceStep::class;
  protected $sequenceStepsDataType = 'array';

  /**
   * @param string
   */
  public function setScope($scope)
  {
    $this->scope = $scope;
  }
  /**
   * @return string
   */
  public function getScope()
  {
    return $this->scope;
  }
  /**
   * @param string
   */
  public function setSequenceMaximumDuration($sequenceMaximumDuration)
  {
    $this->sequenceMaximumDuration = $sequenceMaximumDuration;
  }
  /**
   * @return string
   */
  public function getSequenceMaximumDuration()
  {
    return $this->sequenceMaximumDuration;
  }
  /**
   * @param GoogleAnalyticsAdminV1alphaAudienceSequenceFilterAudienceSequenceStep[]
   */
  public function setSequenceSteps($sequenceSteps)
  {
    $this->sequenceSteps = $sequenceSteps;
  }
  /**
   * @return GoogleAnalyticsAdminV1alphaAudienceSequenceFilterAudienceSequenceStep[]
   */
  public function getSequenceSteps()
  {
    return $this->sequenceSteps;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleAnalyticsAdminV1alphaAudienceSequenceFilter::class, 'Google_Service_GoogleAnalyticsAdmin_GoogleAnalyticsAdminV1alphaAudienceSequenceFilter');