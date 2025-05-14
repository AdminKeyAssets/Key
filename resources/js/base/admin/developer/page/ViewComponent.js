import DeveloperView from '../partials/DeveloperView.vue';

export default {
    components: {
        'developer-view': DeveloperView,
    },
    props: ['id', 'getSaveDataRoute'],
    render(h) {
        return h(DeveloperView, {
            props: {
                id: this.id,
                getSaveDataRoute: this.getSaveDataRoute
            }
        });
    }
}
