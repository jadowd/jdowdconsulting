<?php
/**
 * Autoloader definition for the Workflow component.
 *
 * @copyright Copyright (C) 2005-2007 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version 1.0.1
 * @filesource
 * @package Workflow
 */

return array(
    'ezcWorkflowException'                     => 'Workflow/exceptions/exception.php',
    'ezcWorkflowDefinitionStorageException'    => 'Workflow/exceptions/definition_storage.php',
    'ezcWorkflowExecutionException'            => 'Workflow/exceptions/execution.php',
    'ezcWorkflowInvalidInputException'         => 'Workflow/exceptions/invalid_input.php',
    'ezcWorkflowInvalidWorkflowException'      => 'Workflow/exceptions/invalid_workflow.php',
    'ezcWorkflowVisitable'                     => 'Workflow/interfaces/visitable.php',
    'ezcWorkflowNode'                          => 'Workflow/interfaces/node.php',
    'ezcWorkflowCondition'                     => 'Workflow/interfaces/condition.php',
    'ezcWorkflowNodeBranch'                    => 'Workflow/interfaces/node_branch.php',
    'ezcWorkflowNodeMerge'                     => 'Workflow/interfaces/node_merge.php',
    'ezcWorkflowConditionBooleanSet'           => 'Workflow/interfaces/condition_boolean_set.php',
    'ezcWorkflowConditionComparison'           => 'Workflow/interfaces/condition_comparison.php',
    'ezcWorkflowConditionType'                 => 'Workflow/interfaces/condition_type.php',
    'ezcWorkflowDefinitionStorage'             => 'Workflow/interfaces/definition_storage.php',
    'ezcWorkflowExecution'                     => 'Workflow/interfaces/execution.php',
    'ezcWorkflowNodeArithmeticBase'            => 'Workflow/interfaces/node_arithmetic_base.php',
    'ezcWorkflowNodeConditionalBranch'         => 'Workflow/interfaces/node_conditional_branch.php',
    'ezcWorkflowNodeSynchronization'           => 'Workflow/nodes/control_flow/synchronization.php',
    'ezcWorkflowVisitor'                       => 'Workflow/interfaces/visitor.php',
    'ezcWorkflow'                              => 'Workflow/workflow.php',
    'ezcWorkflowConditionAnd'                  => 'Workflow/conditions/and.php',
    'ezcWorkflowConditionIsAnything'           => 'Workflow/conditions/is_anything.php',
    'ezcWorkflowConditionIsArray'              => 'Workflow/conditions/is_array.php',
    'ezcWorkflowConditionIsBool'               => 'Workflow/conditions/is_bool.php',
    'ezcWorkflowConditionIsEqual'              => 'Workflow/conditions/is_equal.php',
    'ezcWorkflowConditionIsEqualOrGreaterThan' => 'Workflow/conditions/is_equal_or_greater_than.php',
    'ezcWorkflowConditionIsEqualOrLessThan'    => 'Workflow/conditions/is_equal_or_less_than.php',
    'ezcWorkflowConditionIsFalse'              => 'Workflow/conditions/is_false.php',
    'ezcWorkflowConditionIsFloat'              => 'Workflow/conditions/is_float.php',
    'ezcWorkflowConditionIsGreaterThan'        => 'Workflow/conditions/is_greater_than.php',
    'ezcWorkflowConditionIsInteger'            => 'Workflow/conditions/is_integer.php',
    'ezcWorkflowConditionIsLessThan'           => 'Workflow/conditions/is_less_than.php',
    'ezcWorkflowConditionIsNotEqual'           => 'Workflow/conditions/is_not_equal.php',
    'ezcWorkflowConditionIsObject'             => 'Workflow/conditions/is_object.php',
    'ezcWorkflowConditionIsString'             => 'Workflow/conditions/is_string.php',
    'ezcWorkflowConditionIsTrue'               => 'Workflow/conditions/is_true.php',
    'ezcWorkflowConditionNot'                  => 'Workflow/conditions/not.php',
    'ezcWorkflowConditionOr'                   => 'Workflow/conditions/or.php',
    'ezcWorkflowConditionVariable'             => 'Workflow/conditions/variable.php',
    'ezcWorkflowConditionXor'                  => 'Workflow/conditions/xor.php',
    'ezcWorkflowDefinitionStorageXml'          => 'Workflow/definition_storage/xml.php',
    'ezcWorkflowExecutionListener'             => 'Workflow/interfaces/execution_listener.php',
    'ezcWorkflowExecutionNonInteractive'       => 'Workflow/execution/non_interactive.php',
    'ezcWorkflowNodeAction'                    => 'Workflow/nodes/action.php',
    'ezcWorkflowNodeDiscriminator'             => 'Workflow/nodes/control_flow/discriminator.php',
    'ezcWorkflowNodeEnd'                       => 'Workflow/nodes/end.php',
    'ezcWorkflowNodeExclusiveChoice'           => 'Workflow/nodes/control_flow/exclusive_choice.php',
    'ezcWorkflowNodeInput'                     => 'Workflow/nodes/variables/input.php',
    'ezcWorkflowNodeMultiChoice'               => 'Workflow/nodes/control_flow/multi_choice.php',
    'ezcWorkflowNodeParallelSplit'             => 'Workflow/nodes/control_flow/parallel_split.php',
    'ezcWorkflowNodeSimpleMerge'               => 'Workflow/nodes/control_flow/simple_merge.php',
    'ezcWorkflowNodeStart'                     => 'Workflow/nodes/start.php',
    'ezcWorkflowNodeSubWorkflow'               => 'Workflow/nodes/sub_workflow.php',
    'ezcWorkflowNodeSynchronizingMerge'        => 'Workflow/nodes/control_flow/synchronizing_merge.php',
    'ezcWorkflowNodeVariableAdd'               => 'Workflow/nodes/variables/add.php',
    'ezcWorkflowNodeVariableDecrement'         => 'Workflow/nodes/variables/decrement.php',
    'ezcWorkflowNodeVariableDiv'               => 'Workflow/nodes/variables/div.php',
    'ezcWorkflowNodeVariableIncrement'         => 'Workflow/nodes/variables/increment.php',
    'ezcWorkflowNodeVariableMul'               => 'Workflow/nodes/variables/mul.php',
    'ezcWorkflowNodeVariableSet'               => 'Workflow/nodes/variables/set.php',
    'ezcWorkflowNodeVariableSub'               => 'Workflow/nodes/variables/sub.php',
    'ezcWorkflowNodeVariableUnset'             => 'Workflow/nodes/variables/unset.php',
    'ezcWorkflowServiceObject'                 => 'Workflow/interfaces/service_object.php',
    'ezcWorkflowUtil'                          => 'Workflow/util.php',
    'ezcWorkflowVariableHandler'               => 'Workflow/interfaces/variable_handler.php',
    'ezcWorkflowVisitorNodeCollector'          => 'Workflow/visitors/node_collector.php',
    'ezcWorkflowVisitorVerification'           => 'Workflow/visitors/verification.php',
    'ezcWorkflowVisitorVisualization'          => 'Workflow/visitors/visualization.php',
);
?>