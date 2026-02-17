/**
 * Navigation Block - JSON-first CSR router.
 *
 * Vanilla TypeScript runtime (no WordPress interactivity globals).
 * This avoids runtime coupling to window.wp.interactivity.
 *
 * @package
 */

declare global {
	interface Window {
		interactivityTheme?: { apiBase?: string };
	}
}

type RouteType = 'home' | 'post' | 'page' | 'category' | 'tag' | 'search';

interface RouteRequest {
	type: RouteType;
	page?: number;
	slug?: string;
	search?: string;
}

interface RenderResult {
	html: string;
	title: string;
	bodyClasses?: string[];
	postId?: number;
}

interface NavigateOptions {
	fromPopState?: boolean;
	replaceState?: boolean;
	skipScroll?: boolean;
}

interface NavigationState {
	isMenuOpen: boolean;
	isNavigating: boolean;
	currentUrl: string;
	error: string;
}

const API_BASE =
	typeof window !== 'undefined' && window.interactivityTheme?.apiBase
		? window.interactivityTheme.apiBase
		: `${ window.location.origin }/wp-json/wp/v2`;
const ROUTE_MOUNT_SELECTOR = '[data-theme-route-mount]';
const CACHE_STORAGE_KEY = 'theme_route_cache';
const CACHE_MAX_AGE_MS = 5 * 60 * 1000; // 5 minutes
const CACHE_MAX_SIZE = 50; // Max 50 routes

interface CachedRoute {
	html: string;
	title: string;
	bodyClasses?: string[];
	postId?: number;
	timestamp: number;
}

function getLocalStorageCache(): Map< string, CachedRoute > {
	const cache = new Map< string, CachedRoute >();
	try {
		const stored = localStorage.getItem( CACHE_STORAGE_KEY );
		if ( stored ) {
			const parsed = JSON.parse( stored ) as Record<
				string,
				CachedRoute
			>;
			const now = Date.now();
			for ( const [ key, value ] of Object.entries( parsed ) ) {
				if ( now - value.timestamp < CACHE_MAX_AGE_MS ) {
					cache.set( key, value );
				}
			}
		}
	} catch {
		// Ignore localStorage errors
	}
	return cache;
}

function saveToLocalStorage( cache: Map< string, CachedRoute > ): void {
	try {
		const obj: Record< string, CachedRoute > = {};
		cache.forEach( ( value, key ) => {
			obj[ key ] = value;
		} );
		localStorage.setItem( CACHE_STORAGE_KEY, JSON.stringify( obj ) );
	} catch {
		// Ignore quota exceeded or other errors
	}
}

const routeCache = getLocalStorageCache();
const navigationState: NavigationState = {
	isMenuOpen: false,
	isNavigating: false,
	currentUrl: window.location.href,
	error: '',
};

let globalListenersAttached = false;
let baseBodyClasses: string[] = [];
let globalStylesInjected = false;

function captureBaseBodyClasses(): void {
	if ( baseBodyClasses.length > 0 ) {
		return;
	}
	const current = document.body.className.split( /\s+/ ).filter( Boolean );
	const preserve = [
		'site',
		'logged-in',
		'admin-bar',
		'wp-custom-logo',
		'no-customize-support',
	];
	baseBodyClasses = current.filter( ( c ) =>
		preserve.some( ( p ) => c.startsWith( p ) || c === p )
	);
}

function applyBodyClasses( routeClasses: string[] | undefined ): void {
	captureBaseBodyClasses();
	const all = [ ...baseBodyClasses, ...( routeClasses || [] ) ];
	document.body.className = [ ...new Set( all ) ]
		.filter( Boolean )
		.join( ' ' );
}

async function injectGlobalStyles(): Promise< void > {
	if ( globalStylesInjected ) {
		return;
	}
	try {
		const res = await fetch( `${ API_BASE }/theme/global-styles` );
		if ( ! res.ok ) {
			return;
		}
		const data = ( await res.json() ) as { css?: string };
		if ( data.css ) {
			const el = document.createElement( 'style' );
			el.id = 'theme-global-styles';
			el.textContent = data.css;
			document.head.appendChild( el );
			globalStylesInjected = true;
		}
	} catch {
		// Ignore
	}
}

interface AssetItem {
	handle: string;
	src: string;
}

const loadedAssets = new Set< string >();

async function injectPostAssets( postId: number ): Promise< void > {
	try {
		const res = await fetch(
			`${ API_BASE }/theme/post-assets?post_id=${ postId }`
		);
		if ( ! res.ok ) {
			return;
		}
		const data = ( await res.json() ) as {
			styles?: AssetItem[];
			scripts?: AssetItem[];
		};
		const styles = data.styles || [];
		const scripts = data.scripts || [];
		for ( const s of styles ) {
			const key = `style:${ s.src }`;
			if ( loadedAssets.has( key ) ) {
				continue;
			}
			if ( document.querySelector( `link[href="${ s.src }"]` ) ) {
				continue;
			}
			const link = document.createElement( 'link' );
			link.rel = 'stylesheet';
			link.href = s.src;
			link.id = s.handle;
			document.head.appendChild( link );
			loadedAssets.add( key );
		}
		for ( const s of scripts ) {
			const key = `script:${ s.src }`;
			if ( loadedAssets.has( key ) ) {
				continue;
			}
			if ( document.querySelector( `script[src="${ s.src }"]` ) ) {
				continue;
			}
			const script = document.createElement( 'script' );
			script.src = s.src;
			script.id = s.handle;
			script.async = true;
			document.body.appendChild( script );
			loadedAssets.add( key );
		}
	} catch {
		// Ignore
	}
}

function getSiteTitle(): string {
	const siteTitleNode = document.querySelector(
		'.site-title a, .site-title'
	);
	return siteTitleNode?.textContent?.trim() || 'Site';
}

function getMountElement(): HTMLElement | null {
	return document.querySelector( ROUTE_MOUNT_SELECTOR ) as HTMLElement | null;
}

function getNavContainer(): HTMLElement | null {
	return document.querySelector( '#site-navigation' ) as HTMLElement | null;
}

function getMenuToggleButton(): HTMLButtonElement | null {
	return document.querySelector(
		'#site-navigation .menu-toggle'
	) as HTMLButtonElement | null;
}

function getMenuList(): HTMLElement | null {
	return document.querySelector(
		'#site-navigation ul'
	) as HTMLElement | null;
}

function applyMenuState(): void {
	const menuList = getMenuList();
	const menuToggle = getMenuToggleButton();

	if ( menuList ) {
		menuList.classList.toggle( 'is-open', navigationState.isMenuOpen );
	}

	if ( menuToggle ) {
		menuToggle.setAttribute(
			'aria-expanded',
			navigationState.isMenuOpen ? 'true' : 'false'
		);
	}

	document.body.style.overflow = navigationState.isMenuOpen ? 'hidden' : '';
}

function closeMenu(): void {
	navigationState.isMenuOpen = false;
	applyMenuState();
}

function toggleMenu(): void {
	navigationState.isMenuOpen = ! navigationState.isMenuOpen;
	applyMenuState();
}

function toAbsoluteUrl( href: string ): URL {
	return new URL( href, window.location.href );
}

function normalizePath( pathname: string ): string {
	if ( pathname === '/' ) {
		return '/';
	}
	return pathname.replace( /\/+$/, '' ) || '/';
}

function isEligibleInternalLink(
	link: HTMLAnchorElement,
	event?: MouseEvent
): boolean {
	const rawHref = link.getAttribute( 'href' );
	if ( ! rawHref ) {
		return false;
	}
	if ( link.hasAttribute( 'download' ) ) {
		return false;
	}
	if ( rawHref.startsWith( '#' ) ) {
		return false;
	}
	if ( link.getAttribute( 'target' ) === '_blank' ) {
		return false;
	}
	if ( link.closest( '[data-no-csr]' ) ) {
		return false;
	}

	if (
		event &&
		( event.metaKey || event.ctrlKey || event.shiftKey || event.altKey )
	) {
		return false;
	}

	const url = toAbsoluteUrl( rawHref );
	if ( url.origin !== window.location.origin ) {
		return false;
	}
	if (
		url.pathname.startsWith( '/wp-admin' ) ||
		url.pathname.startsWith( '/wp-login.php' )
	) {
		return false;
	}

	return true;
}

async function fetchJson< T >( path: string ): Promise< T > {
	const response = await fetch( `${ API_BASE }${ path }`, {
		credentials: 'same-origin',
		headers: {
			Accept: 'application/json',
		},
	} );
	if ( ! response.ok ) {
		throw new Error( `Request failed: ${ response.status }` );
	}
	return ( await response.json() ) as T;
}

function resolveRoute( url: URL ): RouteRequest {
	const searchQuery = url.searchParams.get( 's' );

	if ( searchQuery ) {
		return {
			type: 'search',
			search: searchQuery,
			page: Number( url.searchParams.get( 'page' ) || 1 ),
		};
	}

	const path = normalizePath( url.pathname );
	const homePaginationMatch = path.match( /^\/page\/(\d+)$/ );
	if ( path === '/' || homePaginationMatch ) {
		return {
			type: 'home',
			page: homePaginationMatch ? Number( homePaginationMatch[ 1 ] ) : 1,
		};
	}

	const categoryMatch = path.match( /^\/category\/([^/]+)$/ );
	if ( categoryMatch ) {
		return {
			type: 'category',
			slug: decodeURIComponent( categoryMatch[ 1 ] ),
			page: 1,
		};
	}

	const tagMatch = path.match( /^\/tag\/([^/]+)$/ );
	if ( tagMatch ) {
		return {
			type: 'tag',
			slug: decodeURIComponent( tagMatch[ 1 ] ),
			page: 1,
		};
	}

	const slug = path.split( '/' ).filter( Boolean ).pop();
	if ( slug ) {
		return { type: 'post', slug: decodeURIComponent( slug ) };
	}

	return { type: 'home', page: 1 };
}

/**
 * Fetch server-rendered HTML from theme (Gutenberg design).
 * No client-side template building - design comes from theme/template-parts.
 * @param absoluteUrl
 */
async function fetchAndRenderRoute(
	absoluteUrl: URL
): Promise< RenderResult > {
	const path = absoluteUrl.pathname.replace( /^\/|\/$/g, '' ) || '';
	const params = new URLSearchParams();
	params.set( 'path', path );
	const page = absoluteUrl.searchParams.get( 'page' );
	if ( page ) {
		params.set( 'page', page );
	}
	const search = absoluteUrl.searchParams.get( 's' );
	if ( search ) {
		params.set( 's', search );
	}

	const data = await fetchJson< {
		html: string;
		title: string;
		bodyClasses?: string[];
		postId?: number;
	} >( `/theme/route-html?${ params.toString() }` );

	return {
		html: data.html,
		title: data.title,
		bodyClasses: data.bodyClasses,
		postId: data.postId,
	};
}

async function runNavigation(
	href: string,
	options: NavigateOptions = {}
): Promise< void > {
	const absoluteUrl = toAbsoluteUrl( href );
	const routeKey = `${ absoluteUrl.pathname }${ absoluteUrl.search }`;
	const mount = getMountElement();

	if ( ! mount ) {
		window.location.href = absoluteUrl.href;
		return;
	}

	navigationState.isNavigating = true;
	navigationState.error = '';
	document.body.classList.add( 'isNavigating' );

	window.dispatchEvent(
		new CustomEvent( 'theme:route:before', {
			detail: { url: absoluteUrl.href },
		} )
	);

	try {
		const route = resolveRoute( absoluteUrl );
		const isSingular = route.type === 'post' || route.type === 'page';
		const cached = ! isSingular ? routeCache.get( routeKey ) : undefined;
		let rendered: RenderResult;
		if ( cached ) {
			rendered = {
				html: cached.html,
				title: cached.title,
				bodyClasses: cached.bodyClasses,
				postId: cached.postId,
			};
		} else {
			rendered = await fetchAndRenderRoute( absoluteUrl );
			// Don't cache singular posts/pages - they have dynamic content (comments) that can change
			if ( ! isSingular ) {
				const newCached: CachedRoute = {
					html: rendered.html,
					title: rendered.title,
					bodyClasses: rendered.bodyClasses,
					postId: rendered.postId,
					timestamp: Date.now(),
				};
				routeCache.set( routeKey, newCached );
				// Trim cache if too large
				if ( routeCache.size > CACHE_MAX_SIZE ) {
					const firstKey = routeCache.keys().next().value;
					if ( firstKey ) {
						routeCache.delete( firstKey );
					}
				}
				saveToLocalStorage( routeCache );
			}
		}

		mount.innerHTML = rendered.html;
		applyBodyClasses( rendered.bodyClasses );
		if ( rendered.postId ) {
			void injectPostAssets( rendered.postId );
		}
		document.title = `${ rendered.title } - ${ getSiteTitle() }`;
		navigationState.currentUrl = absoluteUrl.href;

		if ( options.fromPopState ) {
			if ( options.replaceState ) {
				window.history.replaceState( {}, '', absoluteUrl.href );
			}
		} else if ( options.replaceState ) {
			window.history.replaceState( {}, '', absoluteUrl.href );
		} else {
			window.history.pushState( {}, '', absoluteUrl.href );
		}

		if ( ! options.skipScroll ) {
			window.scrollTo( { top: 0, behavior: 'smooth' } );
		}

		window.dispatchEvent(
			new CustomEvent( 'theme:route:changed', {
				detail: {
					url: absoluteUrl.href,
					routeType: resolveRoute( absoluteUrl ).type,
				},
			} )
		);
	} catch ( error ) {
		navigationState.error =
			error instanceof Error ? error.message : 'Navigation failed';
		window.location.href = absoluteUrl.href;
	} finally {
		navigationState.isNavigating = false;
		document.body.classList.remove( 'isNavigating' );
	}
}

function handleNavigationClick( event: MouseEvent ): void {
	const raw = event.target as Node | null;
	const target: Element | null =
		raw instanceof Element ? raw : raw?.parentElement ?? null;
	if ( ! target ) {
		return;
	}

	const toggleButton = target.closest( '.menu-toggle' );
	if ( toggleButton ) {
		event.preventDefault();
		toggleMenu();
		return;
	}

	const link = target.closest( 'a' ) as HTMLAnchorElement | null;
	if ( ! link ) {
		return;
	}
	if ( ! isEligibleInternalLink( link, event ) ) {
		return;
	}

	event.preventDefault();
	closeMenu();
	void runNavigation( link.href );
}

function initializeNavigationRuntime(): void {
	if ( globalListenersAttached ) {
		return;
	}
	globalListenersAttached = true;

	// Handle SPA navigation (dispatched by inline script in wp_head - runs before any plugins)
	window.addEventListener( 'theme:spa:navigate', ( e: Event ) => {
		const detail = ( e as CustomEvent< { href: string } > ).detail;
		if ( detail?.href ) {
			closeMenu();
			void runNavigation( detail.href );
		}
	} );

	// Fallback: direct click handler (inline script may be blocked by CSP etc.)
	document.addEventListener( 'click', handleNavigationClick, true );

	window.addEventListener( 'popstate', () => {
		void runNavigation( window.location.href, {
			fromPopState: true,
			skipScroll: true,
			replaceState: true,
		} );
	} );

	document.addEventListener( 'keydown', ( event: KeyboardEvent ) => {
		if ( event.key === 'Escape' && navigationState.isMenuOpen ) {
			closeMenu();
		}
	} );

	// Menu state only if nav exists
	const nav = getNavContainer();
	if ( nav ) {
		applyMenuState();
	}
	void injectGlobalStyles();
}

if ( document.readyState === 'loading' ) {
	document.addEventListener(
		'DOMContentLoaded',
		initializeNavigationRuntime
	);
} else {
	initializeNavigationRuntime();
}

export {};
