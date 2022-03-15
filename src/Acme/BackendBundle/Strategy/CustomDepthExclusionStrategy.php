<?php

namespace Acme\BackendBundle\Strategy;

use JMS\Serializer\Context;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;

class CustomDepthExclusionStrategy implements ExclusionStrategyInterface {
   
    public function shouldSkipClass(ClassMetadata $metadata, Context $context)
    {
        return $this->isTooDeep($context);
    }

    public function shouldSkipProperty(PropertyMetadata $property, Context $context)
    {
        return $this->isTooDeep($context);
    }

    private function isTooDeep(Context $context)
    {
        $depth = $context->getDepth();
        $metadataStack = $context->getMetadataStack();

        $nthProperty = 0;
        // iterate from the first added items to the lasts
        for ($i = $metadataStack->count() - 1; $i > 0; $i--) {
            $metadata = $metadataStack[$i];
            if ($metadata instanceof PropertyMetadata) {
                $nthProperty++;
                $relativeDepth = $depth - $nthProperty;

                if ($relativeDepth > 2) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
}
