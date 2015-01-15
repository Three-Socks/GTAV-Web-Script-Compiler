editAreaLoader.load_syntax["xsc-asm"] = {
	'DISPLAY_NAME' : 'ASM'
	,'COMMENT_SINGLE' : {1 : '//'}
	,'COMMENT_MULTI' : {'/*' : '*/'}
	,'QUOTEMARKS' : ['"', "'"]
	,'KEYWORD_CASE_SENSITIVE' : false
	,'KEYWORDS' : {
		'attributes' : [
			'nop', 'Add', 'Sub', 'Mult', 'Div', 'Mod', 'Not', 'Neg', 'CmpEQ', 'CmpNE', 'CmpGT', 'CmpGE', 'CmpLT', 
			'CmpLE', 'fAdd', 'fSub', 'fMul', 'fDiv', 'fMod', 'fNeg', 'FCmpEQ', 'FCmpNE', 'FCmpGT', 'FCmpGE', 'FCmpLT', 
			'FCmpLEvAdd', 'vSub', 'vMul', 'vDiv', 'vNeg', 'And', 'Or', 'Xor', 'ItoF', 'FtoI', 'Dup2', 'Push1', 'Push2', 
			'Push3', 'Push', 'fPush', 'Dup', 'Drop', 'CallNative', 'Function', 'Return', 'pGet', 'pSet', 'pPeekSet', 
			'ToStack', 'FromStack', 'ArrayGetP1', 'ArrayGet1', 'ArraySet1', 'pFrame1', 'getF1', 'setF1', 'pStatic1', 
			'StaticGet1', 'StaticSet1', 'Add1', 'Mult1', 'GetStackImmediateP', 'GetImmediateP1', 'GetImmediate1', 
			'SetImmediate1', 'PushS', 'Add2', 'Mult2', 'GetStackImmediateP', 'GetImmediate2', 'SetImmediate2', 
			'ArrayGet2', 'ArraySet2', 'pFrame2', 'getF2', 'SetF2', 'pStatic2', 'StaticGet2', 'StaticSet2', 
			'pGlobal2', 'globalGet2', 'globalSet2', 'Jump', 'JumpFalse', 'JumpNE', 'JumpEQ', 'JumpLE', 
			'JumpLT', 'JumpGE', 'JumpGT', 'Call', 'pGlobal3', 'globalGet3', 'globalSet3', 'pushI24', 
			'Switch', 'PushString', 'GetHash', 'StrCopy', 'ItoS', 'StrAdd', 'StrAddi', 'SnCopy', 
			'Catch', 'Throw', 'pCall', 'push_-1', 'push_0', 'push_1', 'push_2', 'push_3', 'push_4', 
			'push_5', 'push_6', 'push_7', 'fPush_-1', 'fPush_0.0', 'fPush_1.0', 'fPush_2.0', 'fPush_3.0', 
			'fPush_4.0', 'fPush_5.0', 'fPush_6.0', 'fPush_7.0', 'unk_op'
		]
		,'values' : [
			'hi', 'bye'
		]
		,'specials' : [
			'hola', 'adios'
		]
	}
	,'OPERATORS' :[
		'='
	]
	,'DELIMITERS' :[
		'[', ']'
	]
	,'REGEXPS' : {
		'stocklabel' : {
			'search' : '()(:Label_[0-9]+)()'
			,'class' : 'stocklabel'
			,'modifiers' : 'g'
			,'execute' : 'before'
		}
		,'atstocklabel' : {
			'search' : '()(@Label_[0-9]+)()'
			,'class' : 'atstocklabel'
			,'modifiers' : 'g'
			,'execute' : 'before'
		}
		,'customlabel' : {
			'search' : '()(:[^0-9]+.*)()'
			,'class' : 'customlabel'
			,'modifiers' : 'g'
			,'execute' : 'before'
		}
		,'atcustomlabel' : {
			'search' : '()(@[^0-9]+.*)()'
			,'class' : 'atcustomlabel'
			,'modifiers' : 'g'
			,'execute' : 'before'
		}
		,'offset' : {
			'search' : '()(:off_[0-9]+)()'
			,'class' : 'offset'
			,'modifiers' : 'g'
			,'execute' : 'before'
		}
		,'atoffset' : {
			'search' : '()(@off_[0-9]+)()'
			,'class' : 'atoffset'
			,'modifiers' : 'g'
			,'execute' : 'before'
		}
	}
	,'STYLES' : {
		'COMMENTS': 'color: #AAAAAA;'
		,'QUOTESMARKS': 'color: #d66a00;'
		,'KEYWORDS' : {
			'attributes' : 'color: #0000ff;'
			,'values' : 'color: #2B60FF;'
			,'specials' : 'color: #FF0000;'
			}
		,'OPERATORS' : 'color: #60CA00;'
		,'DELIMITERS' : 'color: #60CA00;'
		,'REGEXPS' : {
			'stocklabel': 'color: #ff0000;'
			,'atstocklabel': 'color: #FF00FF;'
			,'customlabel': 'color: #ff0000;'
			,'atcustomlabel': 'color: #FF00FF;'
			,'offset': 'color: #ff0000;'
			,'atoffset': 'color: #FF00FF;'
		}	
	}
};