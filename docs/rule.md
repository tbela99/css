# Rule

implement css rule
## Methods

### getSelector

Return the rule selector

#### Arguments

none

#### Return Type

array

### setSelector

set the rule selector

#### Arguments

array|string

#### Return Type

\TBela\CSS\Element\Rule

### AddSelector

Add a selector to the rule

#### Arguments

array|string

#### Return Type

\TBela\CSS\Element\Rule

### RemoveSelector

remove a selector to the rule

#### Arguments

array|string

#### Return Type

\TBela\CSS\Element\Rule

### AddDeclaration

add css declaration

#### Arguments

- name: _string|\TBela\CSS\Value\Set_ declaration name
- value: _string|\TBela\CSS\Value\Set_ declaration value

#### Return Type

\TBela\CSS\Element\Rule

### Merge

Merge the provided rule into this rule

#### Arguments

\TBela\CSS\Element\Rule

#### Return Type

\TBela\CSS\Element\Rule

### Supports

return true if the provided node can be a child of this rule

#### Arguments

\TBela\CSS\Element

#### Return Type

\TBela\CSS\Element\Rule
