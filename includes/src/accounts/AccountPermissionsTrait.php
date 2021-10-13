<?php
/*\
|*|  -------------------------------------
|*|  --- [  AccountPermissionsTrait  ] ---
|*|  -------------------------------------
|*|
|*|  More information about Oli in the README.md file.
|*|  You can find it in the project repository: https://github.com/matiboux/Oli/
\*/

/*\
|*|  ╒════════════════════════╕
|*|  │ :: TABLE OF CONTENT :: │
|*|  ╞════════════════════════╛
|*|  │
|*|  ├ I. Properties
|*|  │
|*|  └ II. Methods
|*|    ├ 1. General
|*|    ├ 2. Rights Permissions
|*|    └ 3. User Permissions
\*/

namespace Oli\Accounts;

trait AccountPermissionsTrait
{
	#region I. Properties

	#endregion

	#region II. Methods

	/*\
	|*|      -[ WORK IN PROGRESS ]-
	|*|  USER PERMISSIONS WILL BE ADDED
	|*|        IN A FUTURE UPDATE
	|*|     (RESCHEDULED FOR LATER)
	\*/

	#region II. 1. General

	/** Get user own permissions */
	public function getUserOwnPermissions($permission)
	{
	}

	/** Get user permissions */
	public function getUserPermissions($permission)
	{
	}

	/** Is User Permitted */
	public function isUserPermitted($permission)
	{
	}

	#endregion

	#region II. 2. Rights Permissions

	/** Set Right Permissions */
	public function setRightPermissions($permissions, $userRight)
	{
	}

	/** Add Right Permissions */
	public function addRightPermissions($permissions, $userRight)
	{
	}

	/** Remove Right Permissions */
	public function removeRightPermissions($permissions, $userRight)
	{
	}

	/** Delete Right Permissions */
	public function deleteRightPermissions($userRight)
	{
	}

	/** Is Right Permitted */
	public function isRightPermitted($permission)
	{
	}

	#endregion

	#region II. 3. User Permissions

	/** Set User Permissions */
	public function setUserPermissions($permissions, $userRight)
	{
	}

	/** Add User Permissions */
	public function addUserPermissions($permissions, $userRight)
	{
	}

	/** Remove User Permissions */
	public function removeUserPermissions($permissions, $userRight)
	{
	}

	/** Delete User Permissions */
	public function deleteUserPermissions($userRight)
	{
	}

	#endregion

	#endregion
}
