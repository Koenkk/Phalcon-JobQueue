<?php

namespace BasilFX\JobQueue\Adapter\Database\Base;

use Phalcon\Mvc\Model;

/**
 * Base job model.
 *
 * This is an ModelGen annotated model, which means that it will be compiled
 * before use. Refer to the ModelGen documentation for more information.
 *
 * @Table("jobs")
 * @CreationTimeBehavior("created")
 * @ModificationTimeBehavior("modified")
 * @SoftDeleteBehavior("deleted")
 */
abstract class Job extends Model
{
    /**
     * @IdentifierField
     * @Identity
     * @Primary
     * @GetSet
     */
    protected $ID;

    /**
     * @IntegerField(nullable=false)
     * @GetSet
     */
    protected $state;

    /**
     * @StringField(nullable=true)
     * @GetSet
     */
    protected $lock;

    /**
     * @IntegerField(nullable=true)
     * @GetSet
     */
    protected $progress;

    /**
     * @StringField(nullable=false)
     * @GetSet
     */
    protected $action;

    /**
     * @SerializeField(nullable=true)
     * @GetSet
     */
    protected $parameters;

    /**
     * @SerializeField(nullable=true)
     * @GetSet
     */
    protected $result;

    /**
     * @DateTimeField(nullable=false)
     * @GetSet
     */
    protected $created;

    /**
     * @DateTimeField(nullable=true)
     * @GetSet
     */
    protected $modified;

    /**
     * @DateTimeField(nullable=true)
     * @GetSet
     */
    protected $deleted;
}
