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

class GoogleAnalyticsAdminV1alphaAudienceFilterExpressionList extends \Deconf\AIWP\Google\Collection
{
  protected $collection_key = 'filterExpressions';
  protected $filterExpressionsType = GoogleAnalyticsAdminV1alphaAudienceFilterExpression::class;
  protected $filterExpressionsDataType = 'array';

  /**
   * @param GoogleAnalyticsAdminV1alphaAudienceFilterExpression[]
   */
  public function setFilterExpressions($filterExpressions)
  {
    $this->filterExpressions = $filterExpressions;
  }
  /**
   * @return GoogleAnalyticsAdminV1alphaAudienceFilterExpression[]
   */
  public function getFilterExpressions()
  {
    return $this->filterExpressions;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleAnalyticsAdminV1alphaAudienceFilterExpressionList::class, 'Google_Service_GoogleAnalyticsAdmin_GoogleAnalyticsAdminV1alphaAudienceFilterExpressionList');