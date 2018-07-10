<!-- Login Form (absolutely positioned) -->
<div
	id="login-container"
	class="loginForm">

	<form
		name="login-form"
		id="login-form"
		role="form"
		method="post"
		action="">

		<input
			type="text"
			name="email"
			id="email"
			class="form-control"
			placeholder="FAMU Email">

		<input
			type="password"
			name="password"
			id="password"
			class="form-control"
			placeholder="Password"
			style="line-height:.5em; margin-bottom:0;">

		<a href="class_specs/?page=forgotPw" style="line-height:2em;">Forgot your password?</a>

		<input
			type="submit"
			id="login-submit-btn"
			class="btn btn-md btn-primary"
			value="Login">

		<button
			id="loggingIn-btn"
			class="btn btn-md btn-primary login-msg"
			style="display:none;">
			Logging in...
		</button>

		<button
			id="login-failure-btn"
			class="btn btn-md btn-danger login-msg"
			style="display:none;">
			Login Failure
		</button>
	</form>
</div>
