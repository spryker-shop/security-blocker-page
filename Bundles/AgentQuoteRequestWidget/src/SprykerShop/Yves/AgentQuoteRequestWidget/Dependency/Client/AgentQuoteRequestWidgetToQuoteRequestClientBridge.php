<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\AgentQuoteRequestWidget\Dependency\Client;

use Generated\Shared\Transfer\QuoteRequestCollectionTransfer;
use Generated\Shared\Transfer\QuoteRequestFilterTransfer;
use Generated\Shared\Transfer\QuoteRequestResponseTransfer;
use Generated\Shared\Transfer\QuoteRequestTransfer;

class AgentQuoteRequestWidgetToQuoteRequestClientBridge implements AgentQuoteRequestWidgetToQuoteRequestClientInterface
{
    /**
     * @var \Spryker\Client\QuoteRequest\QuoteRequestClientInterface
     */
    protected $quoteRequestClient;

    /**
     * @param \Spryker\Client\QuoteRequest\QuoteRequestClientInterface $quoteRequestClient
     */
    public function __construct($quoteRequestClient)
    {
        $this->quoteRequestClient = $quoteRequestClient;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteRequestTransfer $quoteRequestTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteRequestResponseTransfer
     */
    public function update(QuoteRequestTransfer $quoteRequestTransfer): QuoteRequestResponseTransfer
    {
        return $this->quoteRequestClient->update($quoteRequestTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteRequestFilterTransfer $quoteRequestFilterTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteRequestCollectionTransfer
     */
    public function getQuoteRequestCollectionByFilter(QuoteRequestFilterTransfer $quoteRequestFilterTransfer): QuoteRequestCollectionTransfer
    {
        return $this->quoteRequestClient->getQuoteRequestCollectionByFilter($quoteRequestFilterTransfer);
    }
}
