{include file='mobile/header.tpl' css_file='app_sale.css'}

<div class="ui-tab">
    <ul class="ui-list status-list sale-title-home">
        <li>
            <div class="ui-avatar-s">
				<span style="background-image:url({$face})"></span>
            </div>
            <div class="ui-list-info  sale-title-home-color">
                <h4 >{$name} &nbsp; <lable class="sale-ui-font-size">{$job}</lable></h4>
                <p><i class="ui-icon sale-ui-home"></i>{$company}</p>
            </div>
        </li>
    </ul>
    <div class="ui-grid-select">
        <ul class="ui-grid-halve">
            <li>
				<a href="/frontend/sale/trajectory_list">
					<div id="trajectory" class="ui-border">
						<i class="ui-icon sale-manage sale-path"></i>
						<p>轨迹管理</p>
					</div>
				</a>
            </li>
            <li>
				<a href="/frontend/sale/coustmer_list">
					<div id="coustmer" class="ui-border">
						<i class="ui-icon sale-manage sale-customer"></i>
						<p>客户管理</p>
					</div>
				</a>
            </li>

            <li>
				<a href="/frontend/sale/business_list">
					<div id="business" class="ui-border">
						<i class="ui-icon sale-manage sale-business"></i>
						<p>商机管理</p>
					</div>
				</a>
            </li>
            <li>
				<a href="#">
					<div id="data" class="ui-border sale-ui-border">
						<i class="ui-icon sale-manage sale-data"></i>
						<p>数据管理</p>
					</div>
				</a>
			</li>
        </ul>
    </div>
</div>
{literal}
{/literal}
{include file='mobile/footer.tpl'}